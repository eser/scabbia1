<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mvc;

use Scabbia\Events;
use Scabbia\Extensions;
use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Mvc\ControllerBase;

/**
 * Mvc Extension: Controllers Class
 *
 * @package Scabbia
 * @subpackage Mvc
 * @version 1.1.0
 */
class Controllers
{
    /**
     * @ignore
     */
    public static $root = null;
    /**
     * @ignore
     */
    public static $models = array();
    /**
     * @ignore
     */
    public static $stack = array();


    /*
     * @ignore
     */
    public static function getControllers()
    {
        if (is_null(self::$root)) {
            $tParms = array();
            Events::invoke('registerControllers', $tParms);

            self::$root = new ControllerBase();

            // if autoload is enabled
            // todo: maybe split _ for children
            foreach (Extensions::getSubclasses('Scabbia\\Extensions\\Mvc\\Controller', true) as $tClass) {
                $tPos = strrpos($tClass, '\\');
                if ($tPos !== false) {
                    self::$root->addChildController(substr($tClass, $tPos + 1), $tClass);
                    continue;
                }

                self::$root->addChildController($tClass, $tClass);
            }
        }
    }

    /**
     * @ignore
     */
    public static function loadDatasource($uDatasourceName)
    {
        if (!isset(self::$models[$uDatasourceName])) {
            self::$models[$uDatasourceName] = Datasources::get($uDatasourceName);
        }

        return self::$models[$uDatasourceName];
    }

    /**
     * @ignore
     */
    public static function load($uModelClass, $uDatasource = null)
    {
        if (!isset(self::$models[$uModelClass])) {
            self::$models[$uModelClass] = new $uModelClass ($uDatasource);
        }

        return self::$models[$uModelClass];
    }
}
