<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mvc;

use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Mvc\Controllers;
use Scabbia\Config;

/**
 * Subcontroller Class
 *
 * @package Scabbia
 * @subpackage LayerExtensions
 */
class Subcontroller
{
    /**
     * @ignore
     */
    public $subcontrollers = array();
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
    public $vars = array();


    /**
     * @ignore
     */
    public function render($uAction, $uParams, $uInput)
    {
        $tActionName = $uAction; // strtr($uAction, '/', '_');
        if (is_null($tActionName)) {
            $tActionName = $this->defaultAction;
        }

        if (isset($this->subcontrollers[$tActionName])) {
            if (count($uParams) > 0) {
                $tSubaction = array_shift($uParams);
            } else {
                $tSubaction = null;
            }

            $tInstance = new $this->subcontrollers[$tActionName] ();
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
            if ($tMe->hasMethod($tActionName) && $tMe->getMethod($tActionName)->isPublic()) {
                $tMethod = $tActionName;
                break;
            }

            return false;
        }

        array_push(Controllers::$stack, $this);

        $this->route = array(
            'controller' => get_class($this),
            'action' => $tMethod,
            'params' => $uParams,
            'query' => isset($uInput['query']) ? $uInput['query'] : ''
        );
        $this->view = $this->route['controller'] . '/' . $this->route['action'] . '.' . Config::get('mvc/view/defaultViewExtension', 'php');

        $tReturn = call_user_func_array(array(&$this, $tMethod), $uParams);
        array_pop(Controllers::$stack);

        return $tReturn;
    }

    /**
     * @ignore
     */
    public function addSubcontroller($uAction, $uClass)
    {
        $this->subcontrollers[$uAction] = $uClass;
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
}
