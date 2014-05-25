<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Assets;

use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Mime\Mime;
use Scabbia\Extensions\Views\Views;
use Scabbia\Binder;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Request;
use Scabbia\Io;

/**
 * Assets Extension
 *
 * @package Scabbia
 * @subpackage Assets
 * @version 1.1.0
 *
 * @todo cdn support somehow
 * @todo add new assets like Assets::add('scabbia.js', 'jquery', 'url://')
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
        if (self::$packs === null) {
            self::$packs = Config::get('assets/packList', array());

            foreach (Config::get('assets/fileList', array()) as $tFile) {
                self::$packs[] = array(
                    'partList' => array(array('bindtype' => $tFile['bindtype'], 'name' => $tFile['name'])),
                    'name' => $tFile['name'],
                    'type' => $tFile['type'],
                    'cacheTtl' => isset($tFile['cacheTtl']) ? $tFile['cacheTtl'] : 0
                );
            }

            self::$directories = Config::get('assets/directoryList', array());
        }

        foreach (self::$directories as $tDirectory) {
            $tDirectoryName = rtrim($tDirectory['name'], '/');
            $tLen = strlen($tDirectoryName);

            if (substr(Request::$pathInfo, 0, $tLen) === $tDirectoryName) {
                if (self::getDirectory($tDirectory, substr(Request::$pathInfo, $tLen)) === true) {
                    // to interrupt event-chain execution
                    return true;
                }
            }
        }

        if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) {
            $tSubParts = explode(',', $_SERVER['QUERY_STRING']);
        } else {
            $tSubParts = array();
        }

        if (self::getPack(Request::$pathInfo, $tSubParts) === true) {
            // to interrupt event-chain execution
            return true;
        }

        return null;
    }

    /**
     * @ignore
     */
    public static function getPack($uName, array $uClasses = array())
    {
        foreach (self::$packs as $tPack) {
            if ($tPack['name'] !== $uName) {
                continue;
            }

            $tSelectedPack = $tPack;
            break;
        }

        if (!isset($tSelectedPack)) {
            return false;
        }

        $tCacheTtl = isset($tSelectedPack['cacheTtl']) ? (int)$tSelectedPack['cacheTtl'] : 0;
        $tBinder = new Binder($tSelectedPack['name'], $tSelectedPack['type'], $tCacheTtl, $uClasses);

        $tMimetype = Mime::getType($tBinder->outputType);
        header('Content-Type: ' . $tMimetype, true);
        Http::sendHeaderCache($tCacheTtl);

        foreach ($tSelectedPack['partList'] as $tPart) {
            $tBindType = isset($tPart['bindtype']) ? $tPart['bindtype'] : 'file';
            $tClass = isset($tPart['class']) ? $tPart['class'] : null;

            if ($tBindType === 'function') {
                $tValue = $tPart['name'];
                $tPartType = isset($tPart['parttype']) ? $tPart['parttype'] : $tBinder->outputType;
            } elseif ($tBindType === 'string') {
                $tValue = $tPart['value'];
                $tPartType = isset($tPart['parttype']) ? $tPart['parttype'] : $tBinder->outputType;
            } else {
                $tValue = $tPart['path'];
                $tPartType = isset($tPart['parttype']) ?
                    $tPart['parttype'] :
                    pathinfo($tPart['path'], PATHINFO_EXTENSION);
            }

            $tBinder->add($tBindType, $tPartType, $tValue, $tClass);
        }

        echo $tBinder->output();

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
            if (strlen($tSubDirectory) === 0 || $tSubDirectory[0] === '.') {
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
