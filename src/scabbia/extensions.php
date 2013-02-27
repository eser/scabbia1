<?php

namespace Scabbia;

use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Framework;

/**
 * Extensions manager which extends the framework capabilities with extra routines.
 *
 * @package Scabbia
 *
 * @todo cache the extensions.xml.php array
 */
class Extensions
{
    /**
     * The array of extensions' config files
     */
    public static $configFiles = null;


    /**
     * Loads the extensions module.
     */
    public static function load()
    {
        self::$configFiles = array();

        $tFiles = array();
        Framework::glob(Framework::$corepath . 'src/scabbia/extensions/', null, Framework::GLOB_DIRECTORIES | Framework::GLOB_RECURSIVE, '', $tFiles);
        if (!is_null(Framework::$applicationPath)) {
            Framework::glob(Framework::$applicationPath . 'extensions/', null, Framework::GLOB_DIRECTORIES | Framework::GLOB_RECURSIVE, '', $tFiles);
        }

        foreach ($tFiles as $tFile) {
            if (!is_file($tFile . 'extension.xml.php')) {
                continue;
            }

            $tSubconfig = array();
            Config::loadFile($tSubconfig, $tFile . 'extension.xml.php');
            self::$configFiles[$tSubconfig['/info/name']] = array('path' => $tFile, 'config' => $tSubconfig);

            if (isset($tSubconfig['/eventList'])) {
                foreach ($tSubconfig['/eventList'] as $tLoad) {
                    if ($tLoad['name'] == 'load') {
                        Events::invokeSingle($tLoad['type'], $tLoad['value']);
                        continue;
                    }

                    Events::register($tLoad['name'], $tLoad['type'], $tLoad['value']);
                }
            }
        }
    }
}
