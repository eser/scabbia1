<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Assets;

use Scabbia\Extensions\Cache\Cache;
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
 * @todo integrate with cache extension
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
        $tCacheTtl = isset($tSelectedPack['cacheTtl']) ? $tSelectedPack['cacheTtl'] : 0;
        $tFilename = $uName;
        foreach ($uClasses as $tClassName) {
            $tFilename .= '_' . $tClassName;
        }
        $tFilename .= '.' . $tType;

        $tCompileAge = isset($tSelectedPack['compiledAge']) ? $tSelectedPack['compiledAge'] : 120;
        $tMimetype = Mime::getType($tType);
        header('Content-Type: ' . $tMimetype, true);

        $tOutputFile = Cache::filePath('assets/', $tFilename, $tCompileAge);
        if (Framework::$development >= 1 || !$tOutputFile[0]) {
            $tContent = '';
            foreach ($tSelectedPack['partList'] as $tPart) {
                $tType = isset($tPart['type']) ? $tPart['type'] : 'file';
                $tClass = isset($tPart['class']) ? $tPart['class'] : null;

                if (!is_null($tClass) && !in_array($tClass, $uClasses, true)) {
                    continue;
                }

                if ($tType == 'function') {
                    $tContent .= call_user_func($tPart['name']);
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
                    // $tContent = JSMin::minify($tContent);
                    if (!is_null($tOutputFile[1])) {
                        Io::write($tOutputFile[1], $tContent);
                    }
                    echo $tContent;
                    break;
                case 'text/css':
                    // $tContent = CssMin::minify($tContent);
                    if (!is_null($tOutputFile[1])) {
                        Io::write($tOutputFile[1], $tContent);
                    }
                    echo $tContent;
                    break;
                default:
                    if (!is_null($tOutputFile[1])) {
                        Io::write($tOutputFile[1], $tContent);
                    }
                    echo $tContent;
                    break;
            }
        } else {
            readfile($tOutputFile[1]);
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

        if (isset($uSelectedDirectory['autoViewer'])) {
            if (is_dir($tPath)) {
                $tPath = rtrim($tPath, '/') . '/' . $uSelectedDirectory['autoViewer']['defaultPage'];
            }

            if (isset($uSelectedDirectory['autoViewer']['header'])) {
                Views::viewFile($uSelectedDirectory['autoViewer']['header']);
            }

            Views::viewFile($tPath);

            if (isset($uSelectedDirectory['autoViewer']['footer'])) {
                Views::viewFile($uSelectedDirectory['autoViewer']['footer']);
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
