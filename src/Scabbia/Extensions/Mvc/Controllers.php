<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mvc;

use Scabbia\Events;
use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Mvc\ControllerBase;
use Scabbia\Utils;

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

            // todo: maybe split _ for children
            foreach (Utils::getSubclasses('Scabbia\\Extensions\\Mvc\\Controller', true) as $tClass) {
                if (($tPos = strrpos($tClass, '\\')) !== false) {
                    $tClassName = lcfirst(substr($tClass, $tPos + 1));
                } else {
                    $tClassName = lcfirst($tClass);
                }

                self::$root->addChildController($tClassName, $tClass);
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
