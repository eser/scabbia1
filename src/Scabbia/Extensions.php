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
     * @var array The array of extensions' config files
     */
    public static $configFiles = array();


    /**
     * Loads the extensions module.
     */
    public static function load()
    {
        $tFiles = array();
        Io::glob(
            Framework::$corepath . 'src/Scabbia/Extensions/',
            null,
            Io::GLOB_DIRECTORIES | Io::GLOB_RECURSIVE,
            '',
            $tFiles
        );
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
                        Events::invokeSingle(array($tLoad['type'], $tLoad['value']));
                        continue;
                    }

                    Events::register($tLoad['name'], $tLoad['type'], $tLoad['value']);
                }
            }
        }
    }

    /**
     * Get Subclasses
     *
     * @param string $uClassName name of the parent class
     * @param bool   $uJustKeys  return keys without reflection instances
     *
     * @return array list of sub classes
     */
    public static function getSubclasses($uClassName, $uJustKeys = false)
    {
        $tClasses = array();

        foreach (get_declared_classes() as $tClass) {
            if (!is_subclass_of($tClass, $uClassName)) {
                continue;
            }

            $tReflection = new \ReflectionClass($tClass);
            if ($tReflection->isAbstract()) {
                continue;
            }

            if ($uJustKeys) {
                $tClasses[] = $tClass;
                continue;
            }

            $tClasses[$tClass] = $tReflection;
        }

        return $tClasses;
    }
}
