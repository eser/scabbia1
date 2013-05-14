<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mvc;

use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Mvc\Controllers;
use Scabbia\Extensions;
use Scabbia\Framework;
use Scabbia\Utils;

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
    public static function setController($uControllerInstance, $uActionName, array $uParams = array(), array $uInput = array())
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
            Config::get('mvc/view/defaultViewExtension', 'php');
    }

    /**
     * @ignore
     */
    public static function export($uAjaxOnly = false)
    {
        $tArray = array();

        foreach (Extensions::getSubclasses('Scabbia\\Extensions\\Mvc\\Controller') as $tClassReflection) {
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
}
