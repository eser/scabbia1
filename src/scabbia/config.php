<?php

namespace Scabbia;

use Scabbia\Framework;

/**
 * Configuration class which handles all configuration-based operations.
 *
 * @package Scabbia
 *
 * @todo _node parsing
 */
class Config
{
    /**
     * Default configuration
     */
    public static $default;


    /**
     * Loads the default configuration for the current application.
     *
     * @uses loadFile()
     */
    public static function load()
    {
        $tConfig = array();

        foreach (Framework::glob(Framework::$corepath . 'config/', null, Framework::GLOB_RECURSIVE | Framework::GLOB_FILES) as $tFile) {
            self::loadFile($tConfig, $tFile);
        }

        if (!is_null(Framework::$applicationPath)) {
            foreach (Framework::glob(Framework::$applicationPath . 'config/', null, Framework::GLOB_RECURSIVE | Framework::GLOB_FILES) as $tFile) {
                self::loadFile($tConfig, $tFile);
            }
        }

        return $tConfig;
    }

    /**
     * @ignore
     */
    private static function xmlPassScope(&$uNode)
    {
        if (isset($uNode['endpoint']) && (string)$uNode['endpoint'] != Framework::$endpoint) {
            return false;
        }

        if (isset($uNode['mode'])) {
            if ((string)$uNode['mode'] == 'development') {
                if (Framework::$development < 1) {
                    return false;
                }
            } else {
                if ((string)$uNode['mode'] == 'debug') {
                    if (Framework::$development < 2) {
                        return false;
                    }
                } else {
                    if (Framework::$development >= 1) {
                        return false;
                    }
                }
            }
        }

        if (isset($uNode['phpextension'])) {
            if (!extension_loaded((string)$uNode['phpextension'])) {
                return false;
            }
        }

        if (isset($uNode['phpversion'])) {
            if (!Framework::phpVersion((string)$uNode['phpversion'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @ignore
     */
    private static function xmlProcessChildrenAsArray(\SimpleXMLElement $uNode, $uListElement, &$uContents)
    {
        foreach ($uNode->children() as $tKey => $tNode) {
            if ($tKey == 'scope') {
                if (!self::xmlPassScope($tNode)) {
                    continue; // skip
                }

                self::xmlProcessChildrenAsArray($tNode, $uListElement, $uContents);
                continue;
            }

            if (!is_null($uListElement) && $uListElement == $tKey) {
                self::xmlProcessChildrenAsArray($tNode, null, $uContents[]);
            } else {
                if (substr($tKey, -4) == 'List') {
                    if (!isset($uContents[$tKey])) {
                        $uContents[$tKey] = array();
                    }

                    self::xmlProcessChildrenAsArray($tNode, substr($tKey, 0, -4), $uContents[$tKey]);
                } else {
                    if (!isset($uContents[$tKey])) {
                        if ($tNode->count() > 0) {
                            $uContents[$tKey] = array();
                        } else {
                            $uContents[$tKey] = null;
                        }
                    }

                    self::xmlProcessChildrenAsArray($tNode, null, $uContents[$tKey]);
                }
            }
        }

        if ($uNode->getName() == 'scope') {
            return;
        }

        $tNodeValue = rtrim((string)$uNode);
        if (strlen($tNodeValue) > 0) {
            if (count($uContents) > 0) {
                $uContents['.'] = $tNodeValue;
            } else {
                $uContents = $tNodeValue;
            }
        }
    }

    /**
     * @ignore
     */
    private static function xmlProcessChildrenRecursive(&$uArray, \SimpleXMLElement $uNode)
    {
        static $sNodes = array();
        $tNodeName = $uNode->getName();

        if ($tNodeName == 'scope') {
            $tScope = true;

            if (!self::xmlPassScope($uNode)) {
                return; // skip
            }
        }

        if (!isset($tScope)) {
            array_push($sNodes, $tNodeName);
            $tNodePath = '/' . implode('/', array_slice($sNodes, 1));

            if (substr($tNodeName, -4) == 'List') {
                $tListName = substr($tNodeName, 0, -4);
            }
        }

        if (isset($tListName)) {
            if (!isset($uArray[$tNodePath])) {
                $uArray[$tNodePath] = array();
            }

            self::xmlProcessChildrenAsArray($uNode, $tListName, $uArray[$tNodePath]);
        } else {
            foreach ($uNode->children() as $tNode) {
                self::xmlProcessChildrenRecursive($uArray, $tNode);
            }

            if (!isset($tScope)) {
                $tNodeValue = rtrim((string)$uNode);
                if (strlen($tNodeValue) > 0) {
                    $uArray[$tNodePath] = $tNodeValue;
                }
            }
        }

        if (!isset($tScope)) {
            array_pop($sNodes);
        }
    }

    /**
     * Returns a configuration which is a compilation of a configuration file.
     *
     * @param array  $uConfig   the array which will contain read data
     * @param string $uFile     path of configuration file
     * @param string $uFileType type of configuration file
     *
     * @return array the configuration
     */
    public static function loadFile(&$uConfig, $uFile, $uFileType = 'xml')
    {
        switch ($uFileType) {
            case 'xml':
                $tXmlDom = simplexml_load_file($uFile, null, LIBXML_NOBLANKS | LIBXML_NOCDATA) or exit('Unable to read from config file - ' . $uFile);
                self::xmlProcessChildrenRecursive($uConfig, $tXmlDom);
                break;
            case 'json':
                // strip comments and load file
                // $tJsonData = preg_replace(
                //    '#(//([^\n]*)|/\*([^\*/]*)\*/)#',
                //    '',
                //    file_get_contents($uFile)
                // );

                // $tJsonObject = json_decode($tJsonData);
                // self::jsonProcessChildrenRecursive($uConfig, '', $tJsonObject);
                break;
            case 'php':
                // $tPhpObject = include $uFile;
                // self::phpProcessChildrenRecursive($uConfig, '', $uPhpObject);
                break;
        }
    }

    /**
     * Gets a value from default configuration.
     *
     * @param string $uKey path of the value
     * @param mixed $uDefault default value
     *
     * @return mixed|null the value
     */
    public static function get($uKey, $uDefault = null)
    {
        if (!array_key_exists($uKey, self::$default)) {
            return $uDefault;
        }

        return self::$default[$uKey];
    }
}
