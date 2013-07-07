<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mvc;

use Scabbia\Config;
use Scabbia\Delegate;
use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Logger\LoggerInstance;
use Scabbia\Extensions\Mvc\Controllers;
use Scabbia\Extensions\Views\Views;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Scabbia\Framework;
use Scabbia\Utils;

/**
 * Mvc Extension: ControllerBase Class
 *
 * @package Scabbia
 * @subpackage Mvc
 * @version 1.1.0
 */
class ControllerBase implements LoggerAwareInterface
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
    public $format = null;
    /**
     * @ignore
     */
    public $prerender;
    /**
     * @ignore
     */
    public $postrender;
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
    public $logger;
    /**
     * @ignore
     */
    public $annotations = array();


    /**
     * @ignore
     */
    public function __construct()
    {
        $this->db = Datasources::get(); // default datasource to member 'db'
        $this->logger = new LoggerInstance(get_class($this));

        $this->prerender = new Delegate();
        $this->postrender = new Delegate();

        $tReflection = new \ReflectionClass($this);
        foreach ($tReflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $tMethodReflection) {
            if ($tMethodReflection->class === __CLASS__) {
                continue;
            }

            $tDocComment = $tMethodReflection->getDocComment();
            if (strlen($tDocComment) > 0) {
                $this->annotations[$tMethodReflection->name] = Utils::parseAnnotations($tDocComment);
            }
        }
    }

    /**
     * @ignore
     */
    public function setLogger(LoggerInterface $uLogger)
    {
        $this->logger = $uLogger;
    }

    /**
     * @ignore
     */
    public function render($uAction, array $uParams, array $uInput)
    {
        $tActionName = $uAction; // strtr($uAction, '/', '_');
        if ($tActionName === null || strlen($tActionName) <= 0) {
            $tActionName = $this->defaultAction;
        }

        $tFormat = substr($uInput['format'], 1);
        // @todo not sure on this
        // Framework::$responseFormat = $tFormat;

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
        $tMethods = array(
            $uInput['methodext'] . '_' . $tActionName . '_' . $tFormat,
            $uInput['methodext'] . '_' . $tActionName,
            $uInput['methodext'] . '_otherwise' . '_' . $tFormat,
            $uInput['methodext'] . '_otherwise',
            $uInput['method'] . '_' . $tActionName . '_' . $tFormat,
            $uInput['method'] . '_' . $tActionName,
            $uInput['method'] . '_otherwise' . '_' . $tFormat,
            $uInput['method'] . '_otherwise',
            $tActionName . '_' . $tFormat,
            'otherwise' . '_' . $tFormat,
            $tActionName,
            'otherwise'
        );

        foreach ($tMethods as $tMethod) {
            if ($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
                Controllers::setController($this, $tActionName, $tFormat, $uParams, $uInput);

                $this->prerender->invoke();

                $tReturn = call_user_func_array(array(&$this, $tMethod), $uParams);

                Framework::$responseFormat = $this->format;

                $this->postrender->invoke();

                return $tReturn;
            }
        }

        return false;
    }

    /**
     * @ignore
     */
    public function addChildController($uAction, $uClass)
    {
        // echo $uAction, ' => ', $uClass, '<br />';
        $this->childControllers[$uAction] = $uClass;
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
        $this->vars[$uKey] = &$uValue;
    }

    /**
     * @ignore
     */
    public function setRange(array $uArray)
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

        if ($uMemberName === null) {
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

        if ($uMemberName === null) {
            if (($tPos = strrpos($uModelClass, '\\')) !== false) {
                $uMemberName = substr($uModelClass, $tPos + 1);
            } else {
                $uMemberName = $uModelClass;
            }
        }

        $this->{$uMemberName} = call_user_func_array('Scabbia\\Extensions\\Mvc\\Controllers::load', $uArgs);
    }

    /**
     * @ignore
     */
    public function view($uView = null, $uModel = null)
    {
        Views::viewFile(
            '{app}views/' . ($uView !== null ? $uView : $this->view),
            $uModel !== null ? $uModel : $this->vars
        );
    }

    /**
     * @ignore
     */
    public function viewFile($uView = null, $uModel = null)
    {
        Views::viewFile(
            $uView !== null ? $uView : $this->view,
            $uModel !== null ? $uModel : $this->vars
        );
    }

    /**
     * @ignore
     */
    public function json($uModel = null)
    {
        Views::json(
            $uModel !== null ? $uModel : $this->vars
        );
    }

    /**
     * @ignore
     */
    public function xml($uModel = null)
    {
        Views::xml(
            $uModel !== null ? $uModel : $this->vars
        );
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
