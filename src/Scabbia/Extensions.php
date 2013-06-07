<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
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
        $tExtensionFiles = array();

        foreach (Config::get('extensionList', array()) as $tExtension) {
            $tFile = Framework::$corepath . 'src/Scabbia/Extensions/' . $tExtension . '/extension.json';
            if (file_exists($tFile)) {
                $tExtensionFiles[] = $tFile;
                continue;
            }

            $tFile = Framework::$apppath . 'Extensions/' . $tExtension . '/extension.json';
            if (file_exists($tFile)) {
                $tExtensionFiles[] = $tFile;
                continue;
            }

            throw new \Exception('extension not found - ' . $tExtension);
        }

        $tLastModified = Io::getLastModified($tExtensionFiles);
        $tOutputFile = Io::translatePath('{writable}cache/extensions');

        if (!Framework::$disableCaches && Config::$loadedFromCache && Io::isReadableAndNewer($tOutputFile, $tLastModified)) {
            self::$configFiles = Io::readSerialize($tOutputFile);
        } else {
            foreach ($tExtensionFiles as $tFile) {
                $tSubconfig = array();
                Config::loadFile($tSubconfig, $tFile);
                self::$configFiles[$tSubconfig['info/name']] = $tSubconfig;
            }

            if (!Framework::$readonly) {
                Io::writeSerialize($tOutputFile, self::$configFiles);
            }
        }

        foreach (self::$configFiles as $tSubconfig) {
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
