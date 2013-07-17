<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Panel;

use Scabbia\Extensions\Auth\Auth;
use Scabbia\Extensions\Panel\Controllers\Panel;
use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Io;
use Scabbia\Utils;

/**
 * Panel Extension: Scabbia Section
 *
 * @package Scabbia
 * @subpackage Panel
 * @version 1.1.0
 */
class PanelScabbia
{
    /**
     * @ignore
     */
    public static function index()
    {
        Auth::checkRedirect('user');

        Views::viewFile('{core}views/panel/scabbia/index.php');
    }

    /**
     * @ignore
     */
    public static function debug()
    {
        Auth::checkRedirect('admin');

        Views::viewFile('{core}views/panel/scabbia/debug.php');
    }

    /**
     * Purges the files in given directory.
     * @todo use garbage collector
     */
    public static function purge()
    {
        Auth::checkRedirect('admin');

        $tStart = microtime(true);

        if (Framework::$application !== null) {
            self::purgeFolder(Framework::$application->path . 'writable/cache/');
            self::purgeFolder(Framework::$application->path . 'writable/logs/');
        }

        exit('done in ' . number_format(microtime(true) - $tStart, 4) . ' msec.');
    }

    /**
     * @ignore
     */
    private static function purgeFolder($uFolder)
    {
        $tDirectory = Io::glob($uFolder, null, Io::GLOB_RECURSIVE | Io::GLOB_FILES);

        if ($tDirectory === false) {
            return;
        }

        foreach ($tDirectory as $tFilename) {
            if (substr($tFilename, -1) === '/') {
                continue;
            }

            unlink($tFilename);
        }
    }
}
