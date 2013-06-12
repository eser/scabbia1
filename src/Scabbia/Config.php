<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Framework;
use Scabbia\Io;
use Scabbia\Utils;

/**
 * Configuration class which handles all configuration-based operations.
 *
 * @package Scabbia
 * @version 1.1.0
 */
class Config
{
    /**
     * @var array Default configuration
     */
    public static $default;
    /**
     * @var bool whether the config is read or loaded from cache
     */
    public static $loadedFromCache;


    /**
     * Loads the default configuration for the current application.
     *
     * @uses Config::loadFile()
     * @return array loaded configuration
     */
    public static function load()
    {
        $tConfigFiles = array();

        $tConfigFiles = Io::glob(
            Framework::$corepath . 'config/',
            '*.json',
            Io::GLOB_RECURSIVE | Io::GLOB_FILES,
            '',
            $tConfigFiles
        );

        if (!is_null(Framework::$apppath)) {
            Io::glob(
                Framework::$apppath . 'config/',
                '*.json',
                Io::GLOB_RECURSIVE | Io::GLOB_FILES,
                '',
                $tConfigFiles
            );
        }

        $tLastModified = Io::getLastModified($tConfigFiles);
        $tOutputFile = Io::translatePath('{writable}cache/config');

        if (!Framework::$disableCaches && Io::isReadableAndNewer($tOutputFile, $tLastModified)) {
            self::$loadedFromCache = true;
            return Io::readSerialize($tOutputFile);
        }

        $tConfig = array();
        foreach ($tConfigFiles as $tFile) {
            self::loadFile($tConfig, $tFile);
        }

        if (isset($tConfig['extensionList'])) {
            foreach ($tConfig['extensionList'] as $tExtension) {
                $tFile = Framework::$corepath . 'src/Scabbia/Extensions/' . $tExtension . '/config.json';
                if (file_exists($tFile)) {
                    self::loadFile($tConfig, $tFile);
                    continue;
                }

                $tFile = Framework::$apppath . 'Extensions/' . $tExtension . '/config.json';
                if (file_exists($tFile)) {
                    self::loadFile($tConfig, $tFile);
                    continue;
                }

                throw new \Exception('extension not found - ' . $tExtension);
            }
        }

        self::$loadedFromCache = false;
        if (!Framework::$readonly) {
            Io::writeSerialize($tOutputFile, $tConfig);
        }

        return $tConfig;
    }

    /**
     * Returns a configuration which is a compilation of a configuration file.
     *
     * @param array  $uConfig   the array which will contain read data
     * @param string $uFile     path of configuration file
     *
     * @return array the configuration
     */
    public static function loadFile(&$uConfig, $uFile)
    {
        $tJsonObject = json_decode(Io::read($uFile));

        $tNodeStack = array();
        self::jsonProcessChildrenRecursive($uConfig, $tJsonObject, $tNodeStack);
    }

    /**
     * Loads the json decoded object into an array.
     *
     * @param mixed $uTarget    target reference
     * @param mixed $uNode      source object
     * @param array $tNodeStack stack of nodes
     * @param bool  $uIsArray   whether is an array or not
     * @param bool  $uIsDirect  read directly as an array
     */
    private static function jsonProcessChildrenRecursive(
        &$uTarget,
        $uNode,
        &$tNodeStack,
        $uIsArray = false,
        array $uNodeFlags = array()
    ) {
        if (is_object($uNode) && !in_array('direct', $uNodeFlags)) {
            foreach ($uNode as $tKey => $tSubnode) {
                $tNodeName = explode(':', $tKey);

                if (count($tNodeName) >= 2) {
                    switch($tNodeName[1]) {
                        case 'disabled':
                            continue 2;
                            break;
                        case 'development':
                            if (!Framework::$development) {
                                continue 2;
                            }
                            break;
                        case 'application':
                            if (Framework::$application->name != $tNodeName[2]) {
                                continue 2;
                            }
                            break;
                        case 'phpversion':
                            if (!Utils::phpVersion($tNodeName[2])) {
                                continue 2;
                            }
                            break;
                        case 'phpextension':
                            if (!extension_loaded($tNodeName[2])) {
                                continue 2;
                            }
                            break;
                    }
                }

                array_push($tNodeStack, $tNodeName[0]);
                array_shift($tNodeName);
                self::jsonProcessChildrenRecursive($uTarget, $tSubnode, $tNodeStack, false, $tNodeName);
                array_pop($tNodeStack);
            }
        } else {
            $tNodePath = implode('/', $tNodeStack);

            if ($uIsArray) {
                if (!is_scalar($uNode)) {
                    if (in_array('override', $uNodeFlags)) {
                        $uTarget = array();
                    }

                    foreach ($uNode as $tSubnodeKey => $tSubnode) {
                        $tNewNodeStack = array();
                        if (in_array('direct', $uNodeFlags)) {
                            self::jsonProcessChildrenRecursive(
                                $uTarget[$tSubnodeKey],
                                $tSubnode,
                                $tNewNodeStack,
                                true,
                                $uNodeFlags
                            );
                        } else {
                            self::jsonProcessChildrenRecursive(
                                $uTarget[],
                                $tSubnode,
                                $tNewNodeStack,
                                true,
                                $uNodeFlags
                            );
                        }
                    }
                } else {
                    $uTarget = $uNode;
                }
            } else {
                if (!is_scalar($uNode)) {
                    if (in_array('override', $uNodeFlags) || !isset($uTarget[$tNodePath])) {
                        $uTarget[$tNodePath] = array();
                    }

                    foreach ($uNode as $tSubnodeKey => $tSubnode) {
                        $tNewNodeStack = array();
                        if (in_array('direct', $uNodeFlags)) {
                            self::jsonProcessChildrenRecursive(
                                $uTarget[$tNodePath][$tSubnodeKey],
                                $tSubnode,
                                $tNewNodeStack,
                                true,
                                $uNodeFlags
                            );
                        } else {
                            self::jsonProcessChildrenRecursive(
                                $uTarget[$tNodePath][],
                                $tSubnode,
                                $tNewNodeStack,
                                true,
                                $uNodeFlags
                            );
                        }
                    }
                } else {
                    $uTarget[$tNodePath] = $uNode;
                }
            }
        }
    }

    /**
     * Gets a value from default configuration.
     *
     * @param string    $uKey       path of the value
     * @param mixed     $uDefault   default value
     *
     * @return mixed|null the value
     */
    public static function get($uKey, $uDefault = null)
    {
        if (!isset(self::$default[$uKey])) {
            return $uDefault;
        }

        return self::$default[$uKey];
    }
}
