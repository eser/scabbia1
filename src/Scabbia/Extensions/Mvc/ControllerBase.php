<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mvc;

use Scabbia\Config;
use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Mvc\Controllers;
use Scabbia\Extensions\Views\Views;

/**
 * Mvc Extension: ControllerBase Class
 *
 * @package Scabbia
 * @subpackage Mvc
 * @version 1.1.0
 */
class ControllerBase
{
    /**
     * @ignore
     */
    public $childControllers = array();
    /**
     * @ignore
     */
    public $defaultAction = 'index';
    /**
     * @ignore
     */
    public $view = null;
    /**
     * @ignore
     */
    public $prerender = null;
    /**
     * @ignore
     */
    public $postrender = null;
    /**
     * @ignore
     */
    public $vars = array();
    /**
     * @ignore
     */
    public $route = null;
    /**
     * @ignore
     */
    public $db;


    /**
     * @ignore
     */
    public function __construct()
    {
        $this->db = Datasources::get(); // default datasource to member 'db'
    }

    /**
     * @ignore
     */
    public function render($uAction, $uParams, $uInput)
    {
        $tActionName = strtolower($uAction); // strtr($uAction, '/', '_');
        if (is_null($tActionName) || strlen($tActionName) <= 0) {
            $tActionName = $this->defaultAction;
        }

        if (isset($this->childControllers[$tActionName])) {
            if (count($uParams) > 0) {
                $tSubaction = array_shift($uParams);
            } else {
                $tSubaction = null;
            }

            $tInstance = new $this->childControllers[$tActionName] ();
            return $tInstance->render($tSubaction, $uParams, $uInput);
        }

        $tMe = new \ReflectionClass($this);

        while (true) {
            $tMethod = Request::$methodext . '_' . $tActionName;
            if ($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
                break;
            }

            // fallback
            $tMethod = Request::$method . '_' . $tActionName;
            if ($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
                break;
            }

            // fallback 2
            $tMethod = $tActionName;
            if ($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
                break;
            }

            // fallback 3
            $tMethod = 'otherwise';
            if($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
                break;
            }

            return false;
        }

        array_push(Controllers::$stack, $this);

        $this->route = array(
            'controller' => get_class($this),
            'action' => $tActionName,
            'params' => $uParams,
            'query' => isset($uInput['query']) ? $uInput['query'] : ''
        );
        $this->view = $this->route['controller'] . '/' . $this->route['action'] . '.' . Config::get('mvc/view/defaultViewExtension', 'php');

        if (!is_null($this->prerender)) {
            call_user_func($this->prerender);
        }

        $tReturn = call_user_func_array(array(&$this, $tMethod), $uParams);

        if (!is_null($this->postrender)) {
            call_user_func($this->postrender);
        }
        array_pop(Controllers::$stack);

        return $tReturn;
    }

    /**
     * @ignore
     */
    public function addChildController($uAction, $uClass)
    {
        // echo strtolower($uAction) . " => " . $uClass . '<br />';
        $this->childControllers[strtolower($uAction)] = $uClass;
    }

    /**
     * @ignore
     */
    public function export()
    {
    }

    /**
     * @ignore
     */
    public function get($uKey)
    {
        return $this->vars[$uKey];
    }

    /**
     * @ignore
     */
    public function set($uKey, $uValue)
    {
        $this->vars[$uKey] = $uValue;
    }

    /**
     * @ignore
     */
    public function setRef($uKey, &$uValue)
    {
        $this->vars[$uKey] = $uValue;
    }

    /**
     * @ignore
     */
    public function setRange($uArray)
    {
        foreach ($uArray as $tKey => $tValue) {
            $this->vars[$tKey] = $tValue;
        }
    }

    /**
     * @ignore
     */
    public function remove($uKey)
    {
        unset($this->vars[$uKey]);
    }

    /**
     * @ignore
     */
    public function loadDatasource($uDatasourceName, $uMemberName = null)
    {
        $uArgs = func_get_args();

        if (is_null($uMemberName)) {
            $uMemberName = $uDatasourceName;
        }

        $this->{$uMemberName} = call_user_func_array('Scabbia\\Extensions\\Mvc\\Controllers::loadDatasource', $uArgs);
    }

    /**
     * @ignore
     */
    public function load($uModelClass, $uMemberName = null)
    {
        $uArgs = func_get_args();

        if (is_null($uMemberName)) {
            $uMemberName = $uModelClass;
        }

        $this->{$uMemberName} = call_user_func_array('Scabbia\\Extensions\\Mvc\\Controllers::load', $uArgs);
    }

    /**
     * @ignore
     */
    public function view($uView = null, $uModel = null)
    {
        Views::view(
            !is_null($uView) ? $uView : $this->view,
            !is_null($uModel) ? $uModel : $this->vars
        );
    }

    /**
     * @ignore
     */
    public function viewFile($uView = null, $uModel = null)
    {
        Views::viewFile(
            !is_null($uView) ? $uView : $this->view,
            !is_null($uModel) ? $uModel : $this->vars
        );
    }

    /**
     * @ignore
     */
    public function json($uModel = null)
    {
        Views::json(
            !is_null($uModel) ? $uModel : $this->vars
        );
    }

    /**
     * @ignore
     */
    public function xml($uModel = null)
    {
        Views::xml(
            !is_null($uModel) ? $uModel : $this->vars
        );
    }

    /**
     * @ignore
     */
    public function redirect()
    {
        $uArgs = func_get_args();
        call_user_func_array('Scabbia\\Extensions\\Http\\Http::redirect', $uArgs);
    }

    /**
     * @ignore
     */
    public function end()
    {
        $uArgs = func_get_args();
        call_user_func_array('Scabbia\\Framework::end', $uArgs);
    }
}
