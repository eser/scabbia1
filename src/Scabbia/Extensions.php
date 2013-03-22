<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Framework;
use Scabbia\Io;

/**
 * Extensions manager which extends the framework capabilities with extra routines.
 *
 * @package Scabbia
 * @version 1.1.0
 *
 * @todo caching
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
        Io::glob(Framework::$corepath . 'src/Scabbia/Extensions/', null, Io::GLOB_DIRECTORIES | Io::GLOB_RECURSIVE, '', $tFiles);
        if (!is_null(Framework::$apppath)) {
            Io::glob(Framework::$apppath . 'Extensions/', null, Io::GLOB_DIRECTORIES | Io::GLOB_RECURSIVE, '', $tFiles);
        }

        foreach ($tFiles as $tFile) {
            if (!file_exists($tFile . 'extension.json')) {
                continue;
            }

            $tSubconfig = array();
            Config::loadFile($tSubconfig, $tFile . 'extension.json');
            self::$configFiles[$tSubconfig['info/name']] = array('path' => $tFile, 'config' => $tSubconfig);

            if (isset($tSubconfig['eventList'])) {
                foreach ($tSubconfig['eventList'] as $tLoad) {
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
