<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Blackmore;

use Scabbia\Extensions\Auth\Auth;
use Scabbia\Extensions\Blackmore\Blackmore;
use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Io;
use Scabbia\Utils;

/**
 * Blackmore Extension: Scabbia Section
 *
 * @package Scabbia
 * @subpackage Blackmore
 * @version 1.1.0
 */
class BlackmoreScabbia
{
    /**
     * @ignore
     */
    public static function registerBlackmoreModules(array $uParms)
    {
        $uParms['modules'][Blackmore::DEFAULT_MODULE_INDEX]['actions']['debug'] = array(
            'icon' => 'info-sign',
            'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreScabbia::debug',
            'menutitle' => 'Debug Info'
        );

        $uParms['modules'][Blackmore::DEFAULT_MODULE_INDEX]['actions']['purge'] = array(
            'icon' => 'trash',
            'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreScabbia::purge',
            'menutitle' => 'Purge'
        );
    }

    /**
     * @ignore
     */
    public static function index()
    {
        Auth::checkRedirect('user');

        Views::viewFile('{core}views/blackmore/scabbia/index.php');
    }

    /**
     * @ignore
     */
    public static function debug()
    {
        Auth::checkRedirect('admin');

        Views::viewFile('{core}views/blackmore/scabbia/debug.php');
    }

    /**
     * Purges the files in given directory.
     * @todo use garbage collector
     */
    public static function purge()
    {
        Auth::checkRedirect('admin');

        $tStart = microtime(true);

        self::purgeFolder(Framework::$apppath . 'writable/cache/');
        self::purgeFolder(Framework::$apppath . 'writable/logs/');

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
            if (substr($tFilename, -1) == '/') {
                continue;
            }

            unlink($tFilename);
        }
    }
}
