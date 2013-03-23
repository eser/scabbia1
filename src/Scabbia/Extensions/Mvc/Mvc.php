<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mvc;

use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Mvc\Controllers;
use Scabbia\Extensions\String\String;
use Scabbia\Framework;

/**
 * Mvc Extension
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
class Mvc
{
    /**
     * @ignore
     */
    public static $defaultController;
    /**
     * @ignore
     */
    public static $defaultAction;
    /**
     * @ignore
     */
    public static $link;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$defaultController = Config::get('mvc/defaultController', 'home');
        self::$defaultAction = Config::get('mvc/defaultAction', 'index');
        self::$link = Config::get('mvc/link', '{@siteroot}/{@controller}/{@action}{@params}{@query}');
    }

    /**
     * @ignore
     */
    public static function route(array $uInput)
    {
        $tActualController = $uInput['controller'];
        $tActualParams = trim($uInput['params'], '/');
        $uParams = explode('/', $tActualParams);

        Controllers::getControllers();

        while (true) {
            $tReturn = Controllers::$root->render($tActualController, $uParams, $uInput);
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
    public static function current()
    {
        return end(Controllers::$stack);
    }

    /**
     * @ignore
     */
    public static function export($uAjaxOnly = false)
    {
        $tArray = array();

        foreach (get_declared_classes() as $tClass) {
            if (!is_subclass_of($tClass, 'Scabbia\\Extensions\\Mvc\\Controller')) {
                continue;
            }

            $tReflectedClass = new \ReflectionClass($tClass);
            foreach ($tReflectedClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $tMethod) {
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
}
