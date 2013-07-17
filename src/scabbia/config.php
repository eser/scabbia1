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
     * @throws \Exception if any extension is not found
     * @return array loaded configuration
     */
    public static function load()
    {
        $tConfigFiles = array(
            Framework::$corepath . 'config.json'
        );

        if (Framework::$application !== null) {
            Io::glob(
                Framework::$application->path . 'config/',
                '*.json',
                Io::GLOB_RECURSIVE | Io::GLOB_FILES,
                "",
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
            self::loadFile($tConfig, $tFile, true);
        }

        if (isset($tConfig['extensionList'])) {
            foreach ($tConfig['extensionList'] as $tExtension) {
                $tFile = Framework::$corepath . 'src/Scabbia/Extensions/' . $tExtension . '/config.json';

                if (file_exists($tFile)) {
                    self::loadFile($tConfig, $tFile, false);
                    continue;
                }

                if (Framework::$application !== null) {
                    $tFile = Framework::$application->path . 'Extensions/' . $tExtension . '/config.json';

                    if (file_exists($tFile)) {
                        self::loadFile($tConfig, $tFile, false);
                        continue;
                    }
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
     * @param array  $uConfig    the array which will contain read data
     * @param string $uFile      path of configuration file
     * @param bool   $uOverwrite overwrite existing values
     *
     * @return array the configuration
     */
    public static function loadFile(&$uConfig, $uFile, $uOverwrite = true)
    {
        $tJsonObject = json_decode(Io::read($uFile));

        $tNodeStack = array();
        self::jsonProcessChildrenRecursive($uConfig, $tJsonObject, $uOverwrite, $tNodeStack);
    }

    /**
     * Loads the json decoded object into an array.
     *
     * @param mixed $uTarget    target reference
     * @param mixed $uNode      source object
     * @param bool  $uOverwrite overwrite existing values
     * @param array $tNodeStack stack of nodes
     * @param bool  $uIsArray   whether is an array or not
     * @param array $uNodeFlags flags of the node
     */
    private static function jsonProcessChildrenRecursive(
        &$uTarget,
        $uNode,
        $uOverwrite,
        &$tNodeStack,
        $uIsArray = false,
        array $uNodeFlags = array()
    ) {
        if (is_object($uNode) && !in_array('direct', $uNodeFlags)) {
            foreach ($uNode as $tKey => $tSubnode) {
                $tNodeName = explode(':', $tKey);

                if (count($tNodeName) >= 2) {
                    if ($tNodeName[1] === 'disabled') {
                        continue;
                    } elseif ($tNodeName[1] === 'development') {
                        if (!Framework::$development) {
                            continue;
                        }
                    } elseif ($tNodeName[1] === 'application') {
                        if (Framework::$application->name !== $tNodeName[2]) {
                            continue;
                        }
                    } elseif ($tNodeName[1] === 'phpversion') {
                        if (!Utils::phpVersion($tNodeName[2])) {
                            continue;
                        }
                    } elseif ($tNodeName[1] === 'phpextension') {
                        if (!extension_loaded($tNodeName[2])) {
                            continue;
                        }
                    }
                }

                $tNodeStack[] = $tNodeName[0];
                array_shift($tNodeName);
                self::jsonProcessChildrenRecursive($uTarget, $tSubnode, $uOverwrite, $tNodeStack, false, $tNodeName);
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
                                $uOverwrite,
                                $tNewNodeStack,
                                true,
                                $uNodeFlags
                            );
                        } else {
                            self::jsonProcessChildrenRecursive(
                                $uTarget[],
                                $tSubnode,
                                $uOverwrite,
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
                                $uOverwrite,
                                $tNewNodeStack,
                                true,
                                $uNodeFlags
                            );
                        } else {
                            self::jsonProcessChildrenRecursive(
                                $uTarget[$tNodePath][],
                                $tSubnode,
                                $uOverwrite,
                                $tNewNodeStack,
                                true,
                                $uNodeFlags
                            );
                        }
                    }
                } elseif ($uOverwrite || !isset($uTarget[$tNodePath])) {
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
