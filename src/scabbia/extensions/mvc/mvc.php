<?php

namespace Scabbia\Extensions\Mvc;

use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Http\Response;
use Scabbia\Extensions\String\String;
use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Framework;

/**
 * MVC Extension
 *
 * @package Scabbia
 * @subpackage mvc
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends string, http, router, models, views
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
 *
 * @todo remove underscore '_' in controller, action names
 * @todo forbid 'shared' for controller names
 * @todo controller and action names localizations
 * @todo selective loading with controller imports
 * @todo routing optimizations.
 * @todo map controller to path (/docs/index/* => views/docs/*.md)
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
    public static function route($uInput)
    {
        if (isset($uInput['controller']) && strlen($uInput['controller']) > 0) {
            $tActualController = $uInput['controller'];
        } else {
            $tActualController = self::$defaultController;
        }

        if (isset($uInput['params'])) {
            $tActualParams = trim($uInput['params'], '/');
        } else {
            $tActualParams = '';
        }

        if (strlen($tActualParams) > 0) {
            $uParams = explode('/', $tActualParams);
        } else {
            $uParams = array();
        }

        controllers::getControllers();

        while (true) {
            try {
                $tReturn = controllers::$root->render($tActualController, $uParams, $uInput);
                if ($tReturn === false) {
                    break;
                }

                // call callback/closure returned by render
                if ($tReturn !== true && !is_null($tReturn)) {
                    call_user_func($tReturn);
                    break;
                }
            } catch (\Exception $ex) {
                self::error($ex->getMessage());
                $tReturn = false;
            }

            break;
        }

        return $tReturn;
    }

    /**
     * @ignore
     */
    public static function httpUrl(&$uParms)
    {
        $tResolved = Http::routeResolve($uParms['path']);

        if (is_null($tResolved)) {
            return;
        }

        $uParms['controller'] = isset($tResolved[1]['controller']) ? $tResolved[1]['controller'] : '';
        $uParms['action'] = isset($tResolved[1]['action']) ? $tResolved[1]['action'] : '';
        $uParms['params'] = isset($tResolved[1]['params']) ? $tResolved[1]['params'] : '';
        $uParms['query'] = isset($tResolved[1]['query']) ? $tResolved[1]['query'] : '';
    }

    /**
     * @ignore
     */
    public static function generate($uPath)
    {
        controllers::getControllers();

        $tResolved = Http::routeResolve($uPath);
        if (is_null($tResolved)) {
            return false;
        }

        $tRoute = $tResolved[1];
        $tActualController = $tRoute['controller'];
        $tActualAction = $tRoute['action'];

        $tParameterSegments = null;
        $tParms = array(
            'controller' => &$tRoute['controller'],
            'action' => &$tRoute['action'],
            'controllerActual' => &$tActualController,
            'actionActual' => &$tActualAction,
            'parameterSegments' => &$tParameterSegments
        );
        // Events::invoke('routing', $tParms);

        controllers::getControllers();

        while (true) {
            if (strpos($tActualAction, '_') !== false) {
                $tReturn = false;
                break;
            }

            // $tController = new controllers::$controllerList[$tActualController] ();
            // $tController->route = $uParams;
            // $tController->view = $uParams['controller'] . '/' . $uParams['action'] . '.' . Config::get('mvc/view/defaultViewExtension', 'php');

            try {
                $tReturn = controllers::$root->render($tActualAction, $tRoute['parametersArray']);
                if ($tReturn === false) {
                    break;
                }

                if ($tReturn !== true && !is_null($tReturn)) {
                    call_user_func($tReturn);
                    break;
                }
            } catch (\Exception $ex) {
                self::error($ex->getMessage());
                $tReturn = false;
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
        return end(controllers::$stack);
    }

    /**
     * @ignore
     */
    public static function currentUrl()
    {
        $tCurrent = self::current();

        return String::format(
            self::$link,
            array(
                 'siteroot' => rtrim(Framework::$siteroot, '/'),
                 'device' => Request::$crawlerType,
                 'controller' => $tCurrent->route['controller'],
                 'action' => $tCurrent->route['action'],
                 'parameters' => $tCurrent->route['parameters'],
                 'queryString' => $tCurrent->route['queryString']
            )
        );
    }

    /**
     * @ignore
     */
    private static function urlInternal($uPath)
    {
        $tParms = array(
            'siteroot' => rtrim(Framework::$siteroot, '/'),
            'device' => Request::$crawlerType,
            'path' => $uPath
        );

        Events::invoke('httpUrl', $tParms);

        return String::format(self::$link, $tParms);
    }

    /**
     * @ignore
     */
    public static function url()
    {
        $tArgs = func_get_args();

        return call_user_func_array('Scabbia\\Extensions\\Mvc\\mvc::urlInternal', $tArgs);
    }

    /**
     * @ignore
     */
    public static function redirect()
    {
        $tArgs = func_get_args();
        $tQuery = call_user_func_array('Scabbia\\Extensions\\Mvc\\mvc::urlInternal', $tArgs);

        Response::sendRedirect($tQuery, true);
    }

    /**
     * @ignore
     */
    public static function export($uAjaxOnly = false)
    {
        $tArray = array();

        foreach (get_declared_classes() as $tClass) {
            if (!is_subclass_of($tClass, 'Scabbia\\Extensions\\Mvc\\controller')) { // && $tClass != 'controller'
                continue;
            }

            $tReflectedClass = new \ReflectionClass($tClass);
            foreach ($tReflectedClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $tMethod) {
                if ($tMethod->class == 'controller') {
                    continue;
                }

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
    public static function exportAjaxJs()
    {
        $tArray = self::export(true);

        $tReturn = <<<EOD
\$l.ready(function() {
    \$l.extend({
EOD;
        foreach ($tArray as $tClassName => $tClass) {
            $tLines = array();

            if (isset($tFirst)) {
                $tReturn .= ',';
            } else {
                $tFirst = false;
            }

            $tReturn .= PHP_EOL . "\t\t\t" . $tClassName . ': {' . PHP_EOL;

            foreach ($tClass as $tMethod) {
                $tMethods = explode('_', $tMethod, 2);
                if (count($tMethods) < 2 || strpos($tMethods[0], 'ajax') === false) {
                    continue;
                }

                $tLines[] = "\t\t\t\t" . $tMethods[1] . ': function(values, fnc) { $l.ajax.post(\'' . self::url($tClassName . '/' . strtr($tMethods[1], '_', '/')) . '\', values, fnc); }';
            }
            $tReturn .= implode(',' . PHP_EOL, $tLines) . PHP_EOL . "\t\t\t" . '}';
        }
        $tReturn .= <<<EOD

    });
});
EOD;

        return $tReturn;
    }
}
