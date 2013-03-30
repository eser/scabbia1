<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Blackmore;

use Scabbia\Extensions\Auth\Auth;
use Scabbia\Extensions\Blackmore\Blackmore;
use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Extensions;
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

        $uParms['modules'][Blackmore::DEFAULT_MODULE_INDEX]['actions']['build'] = array(
            'icon' => 'download-alt',
            'callback' => 'Scabbia\\Extensions\\Blackmore\\BlackmoreScabbia::build',
            'menutitle' => 'Build'
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
     * Builds a framework compilation.
     */
    public static function build()
    {
        Auth::checkRedirect('admin');

        // $tStart = microtime(true);
        $tFilename = 'compiled.php';
        $tContents = self::buildExport(false);

        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT', true);
        header('Pragma: public', true);
        header('Cache-Control: no-store, no-cache, must-revalidate', true);
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Content-Type: application/octet-stream', true);
        header('Content-Disposition: attachment;filename=' . $tFilename, true);

        echo $tContents;

        // exit('done in ' . number_format(microtime(true) - $tStart, 4) . ' msec.');
    }

    /**
     * @ignore
     */
    private static function buildExport($uPseudo)
    {
        if ($uPseudo) { // Framework::$development >= 1 ||
            $tPseudoCompile = '<' . '?php require ' . var_export('framework.php', true) . '; ?' . '>';

            return $tPseudoCompile;
        }

        /* BEGIN */
        /*
        $tCompiled = Utils::printFile('<' . '?php

ignore_user_abort();

//! todo dump framework variables here.

error_reporting(' . var_export(error_reporting(), true) . ');
ini_set(\'display_errors\', ' . var_export(ini_get('display_errors'), true) . ');
ini_set(\'log_errors\', ' . var_export(ini_get('log_errors'), true) . ');

?' . '>');
        */

        $tCompiled  = Utils::printFile(Io::read(Framework::$corepath . 'src/patches.php'));
        $tCompiled .= Utils::printFile(Io::read(Framework::$corepath . 'src/scabbia/framework.php'));
        $tCompiled .= Utils::printFile(Io::read(Framework::$corepath . 'src/scabbia/config.php'));
        $tCompiled .= Utils::printFile(Io::read(Framework::$corepath . 'src/scabbia/events.php'));
        $tCompiled .= Utils::printFile(Io::read(Framework::$corepath . 'src/scabbia/extensions.php'));

        $tDevelopment = Framework::$development;
        Framework::$development = 0;

        $tConfig = Config::load();
        Extensions::load();
        $tCompiled .= Utils::printFile(
            '<' . '?php Config::$default = ' .
            var_export($tConfig, true) .
            '; Extensions::$configFiles = ' .
            var_export(Extensions::$configFiles, true) .
            '; ?' .
            '>'
        );

        // download files
        if (isset($tConfig['/downloadList'])) {
            foreach ($tConfig['/downloadList'] as $tUrl) {
                Io::downloadFile($tUrl['filename'], $tUrl['url']);
            }
        }

        // include extensions
        $tIncludedFiles = array();

        //! autoloaded extensions?
        foreach ($tConfig['/extensionList'] as $tExtensionName) {
            $tExtension = $tExtensions[$tExtensionName];

            if (isset($tExtension['config']['/includeList'])) {
                foreach ($tExtension['config']['/includeList'] as $tFile) {
                    $tFilename = $tExtension['path'] . $tFile;

                    if (!in_array($tFilename, $tIncludedFiles, true)) {
                        $tCompiled .= Utils::printFile(Io::read($tFilename));
                        $tIncludedFiles[] = $tFilename;
                    }
                }
            }
        }

        // include files
        if (isset($tConfig['/includeList'])) {
            foreach ($tConfig['/includeList'] as $tInclude) {
                $tIncludePath = pathinfo(Io::translatePath($tInclude));

                $tFiles = Io::glob($tIncludePath['dirname'] . '/', $tIncludePath['basename'], Io::GLOB_FILES);
                if ($tFiles !== false) {
                    foreach ($tFiles as $tFilename) {
                        if (substr($tFilename, -1) == '/') {
                            continue;
                        }

                        if (!in_array($tFilename, $tIncludedFiles, true)) {
                            $tCompiled .= Utils::printFile(Io::read($tFilename));
                            $tIncludedFiles[] = $tFilename;
                        }
                    }
                }
            }
        }
        /* END   */

        Framework::$development = $tDevelopment;

        return $tCompiled;
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
