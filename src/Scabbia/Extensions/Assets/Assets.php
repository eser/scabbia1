<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Assets;

use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Http\Response;
use Scabbia\Extensions\Mime\Mime;
use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Extensions;
use Scabbia\Framework;
use Scabbia\Io;
use Scabbia\Utils;

/**
 * Assets Extension
 *
 * @package Scabbia
 * @subpackage Assets
 * @version 1.1.0
 *
 * @todo cdn support somehow
 */
class Assets
{
    /**
     * @ignore
     */
    public static $packs = null;
    /**
     * @ignore
     */
    public static $directories = null;
    /**
     * @ignore
     */
    public static $lessCompiler = null;


    /**
     * @ignore
     */
    public static function routing()
    {
        if (is_null(self::$packs)) {
            self::$packs = Config::get('assets/packList', array());

            foreach (Config::get('assets/fileList', array()) as $tFile) {
                self::$packs[] = array(
                    'partList' => array(array('type' => $tFile['type'], 'name' => $tFile['name'])),
                    'name' => $tFile['name'],
                    'type' => $tFile['type'],
                    'cacheTtl' => isset($tFile['cacheTtl']) ? $tFile['cacheTtl'] : 0
                );
            }

            self::$directories = Config::get('assets/directoryList', array());
        }

        if (strlen(Request::$queryString) > 0) {
            $tPath = explode('&', Request::$queryString, 2);

            foreach (self::$directories as $tDirectory) {
                $tDirectoryName = rtrim($tDirectory['name'], '/');
                $tLen = strlen($tDirectoryName);

                if (substr($tPath[0], 0, $tLen) == $tDirectoryName) {
                    if (self::getDirectory($tDirectory, substr($tPath[0], $tLen)) === true) {
                        // to interrupt event-chain execution
                        return true;
                    }
                }
            }

            $tSubParts = (count($tPath) >= 2) ? explode(',', $tPath[1]) : array();
            if (self::getPack($tPath[0], $tSubParts) === true) {
                // to interrupt event-chain execution
                return true;
            }
        }

        return null;
    }

    /**
     * @ignore
     */
    public static function getPack($uName, array $uClasses = array())
    {
        foreach (self::$packs as $tPack) {
            if ($tPack['name'] != $uName) {
                continue;
            }

            $tSelectedPack = $tPack;
            break;
        }

        if (!isset($tSelectedPack)) {
            return false;
        }

        $tType = $tSelectedPack['type'];
        $tCacheTtl = isset($tSelectedPack['cacheTtl']) ? (int)$tSelectedPack['cacheTtl'] : 0;
        $tMinify = isset($tSelectedPack['minify']) ? (bool)$tSelectedPack['minify'] : true;
        $tFilename = $uName;
        foreach ($uClasses as $tClassName) {
            $tFilename .= '_' . $tClassName;
        }
        $tFilename .= '.' . $tType;

        $tMimetype = Mime::getType($tType);
        header('Content-Type: ' . $tMimetype, true);

        $tCache = Datasources::get('fileCache');
        $tGenerate = (Framework::$development >= 1);

        if (!$tGenerate) {
            $tCachedData = $tCache->cacheGet('assets/' . $tFilename);
        }

        if (!$tGenerate && $tCachedData !== false) {
            echo $tCachedData;
        } else {
            $tContent = '';
            foreach ($tSelectedPack['partList'] as $tPart) {
                $tType = isset($tPart['type']) ? $tPart['type'] : 'file';
                $tClass = isset($tPart['class']) ? $tPart['class'] : null;

                if (!is_null($tClass) && !in_array($tClass, $uClasses, true)) {
                    continue;
                }

                if ($tType == 'function') {
                    $tContent .= call_user_func($tPart['name']);
                } else if ($tType == 'file.less') {
                    if (is_null(self::$lessCompiler)) {
                        self::$lessCompiler = new \lessc();
                    }

                    $tLessScript = Io::translatePath($tPart['path']);

                    $tContent .= '/* CSS: ' . $tPart['path'] . ' (LESS) */' . PHP_EOL;
                    $tContent .= self::$lessCompiler->compileFile($tLessScript);
                    $tContent .= PHP_EOL;
                } else {
                    switch ($tMimetype) {
                        case 'application/x-httpd-php':
                        case 'application/x-httpd-php-source':
                            $tContent .= Utils::printFile(Io::translatePath($tPart['path']));
                            break;
                        case 'application/x-javascript':
                            $tContent .= '/* JS: ' . $tPart['path'] . ' */' . PHP_EOL;
                            $tContent .= Io::read(Io::translatePath($tPart['path']));
                            $tContent .= PHP_EOL;
                            break;
                        case 'text/css':
                            $tContent .= '/* CSS: ' . $tPart['path'] . ' */' . PHP_EOL;
                            $tContent .= Io::read(Io::translatePath($tPart['path']));
                            $tContent .= PHP_EOL;
                            break;
                        default:
                            $tContent .= Io::read(Io::translatePath($tPart['path']));
                            break;
                    }
                }
            }

            Response::sendHeaderCache($tCacheTtl);

            switch ($tMimetype) {
                case 'application/x-javascript':
                    if ($tMinify) {
                        $tContent = \JSMinPlus::minify($tContent);
                    }
                    echo $tContent;
                    break;
                case 'text/css':
                    if ($tMinify) {
                        $tContent = \CssMin::minify($tContent);
                    }
                    echo $tContent;
                    break;
                default:
                    echo $tContent;
                    break;
            }

            $tCache->cacheSet('assets/' . $tFilename, $tContent);
        }

        return true;
    }


    /**
     * @ignore
     *
     * @throws \Exception
     */
    public static function getDirectory($uSelectedDirectory, $uSubPath)
    {
        $tPath = rtrim(Io::translatePath($uSelectedDirectory['path']), '/');

        foreach (explode('/', ltrim($uSubPath, '/')) as $tSubDirectory) {
            if (strlen($tSubDirectory) == 0 || $tSubDirectory[0] == '.') {
                break;
            }

            $tPath .= '/' . $tSubDirectory;
        }

        if (!file_exists($tPath)) {
            throw new \Exception('asset not found.');
        }

        if (isset($uSelectedDirectory['autoViewer/defaultPage'])) {
            if (is_dir($tPath)) {
                $tPath = rtrim($tPath, '/') . '/' . $uSelectedDirectory['autoViewer/defaultPage'];
            }

            if (isset($uSelectedDirectory['autoViewer/header'])) {
                Views::viewFile($uSelectedDirectory['autoViewer/header']);
            }

            Views::viewFile($tPath);

            if (isset($uSelectedDirectory['autoViewer/footer'])) {
                Views::viewFile($uSelectedDirectory['autoViewer/footer']);
            }

            return true;
        }

        if (is_dir($tPath)) {
            return false;
        }

        header('Content-Type: ' . Mime::getType(pathinfo($tPath, PATHINFO_EXTENSION)), true);
        header('Content-Transfer-Encoding: binary', true);
        // header('ETag: "' . md5_file($tPath) . '"', true);

        readfile($tPath);

        return true;
    }
}
