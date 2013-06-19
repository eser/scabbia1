<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mvc;

use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Mvc\ControllerBase;
use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Framework;
use Scabbia\Utils;

/**
 * Mvc Extension: Controllers Class
 *
 * @package Scabbia
 * @subpackage Mvc
 * @version 1.1.0
 *
 * @todo remove underscore '_' in controller, action names
 * @todo forbid 'shared' for controller names
 * @todo controller and action names localizations
 * @todo selective loading with controller imports
 * @todo routing optimizations.
 * @todo map controller to path (/docs/index/* => views/docs/*.md)
 * @todo subcontrollers as 'subcontroller arrays' with subscription
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


    /**
     * @ignore
     */
    public static function route(array $uInput)
    {
        $tActualController = $uInput['controller'];
        $tActualParams = trim($uInput['params'], '/');
        $uParams = explode('/', $tActualParams);

        if (is_null(self::$root)) {
            /*
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
            */

            self::$root = new ControllerBase();
            $tClass = Framework::$application->name . '\\' . $tActualController;

            if (class_exists($tClass, true)) {
                if (($tPos = strrpos($tClass, '\\')) !== false) {
                    $tClassName = lcfirst(substr($tClass, $tPos + 1));
                } else {
                    $tClassName = lcfirst($tClass);
                }

                self::$root->addChildController($tClassName, $tClass);
            }
        }

        while (true) {
            $tReturn = self::$root->render($tActualController, $uParams, $uInput);
            if ($tReturn === false) {
                break;
            }

            // call callback/closure returned by render
            if ($tReturn !== true && !is_null($tReturn)) {
                call_user_func($tReturn);
                break;
            }

            break;
        }

        return $tReturn;
    }

    /**
     * @ignore
     */
    public static function setController($uControllerInstance, $uActionName, $uFormat, array $uParams = array(), array $uInput = array())
    {
        Utils::$variables['controller'] = $uControllerInstance;

        $uControllerInstance->route = array(
            'controller' => get_class($uControllerInstance),
            'action' => $uActionName,
            'params' => $uParams,
            'query' => !isset($uInput['query']) ? $uInput['query'] : ''
        );

        if (($tPos = strrpos($uControllerInstance->route['controller'], '\\')) !== false) {
            $uControllerInstance->route['controller'] = substr($uControllerInstance->route['controller'], $tPos + 1);
        }

        $uControllerInstance->view = $uControllerInstance->route['controller'] .
            '/' .
            $uControllerInstance->route['action'] .
            '.' .
            Config::get('views/defaultViewExtension', 'php');

        $uControllerInstance->format = $uFormat;
    }

    /**
     * @ignore
     */
    public static function export($uAjaxOnly = false)
    {
        $tArray = array();

        foreach (Utils::getSubclasses('Scabbia\\Extensions\\Mvc\\Controller') as $tClassReflection) {
            foreach ($tClassReflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $tMethod) {
                // if ($tMethod->class == 'Controller') {
                //    continue;
                // }

                $tPos = strpos($tMethod->name, 'ajax_');
                if ($uAjaxOnly && $tPos === false) {
                    continue;
                }

                if (!isset($tArray[$tMethod->class])) {
                    $tArray[$tMethod->class] = array();
                }

                $tArray[$tMethod->class][] = $tMethod->name;
            }
        }

        return $tArray;
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
