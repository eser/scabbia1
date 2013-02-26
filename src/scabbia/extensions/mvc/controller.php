<?php

namespace Scabbia\Extensions\Mvc;

use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Io\Io;
use Scabbia\Extensions\Mvc\Subcontroller;
use Scabbia\Extensions\Views\Views;
use Scabbia\Extensions;
use Scabbia\Framework;

/**
 * Controller Class
 *
 * @package Scabbia
 * @subpackage LayerExtensions
 */
abstract class Controller extends Subcontroller
{
    /**
     * @ignore
     */
    public $route = null;
    /**
     * @ignore
     */
    public $view = null;
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
    public function mapDirectory($uDirectory, $uExtension, $uAction, $uArgs)
    {
        $tMap = Io::mapFlatten(Framework::translatePath($uDirectory), '*' . $uExtension, true, true);

        array_unshift($uArgs, $uAction);
        $tPath = implode('/', $uArgs);

        if (in_array($tPath, $tMap, true)) {
            $this->view($uDirectory . $tPath . $uExtension);

            return true;
        }

        return false;
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

        $this->{$uMemberName} = call_user_func_array('Scabbia\\Extensions\\Mvc\\controllers::loadDatasource', $uArgs);
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

        $this->{$uMemberName} = call_user_func_array('Scabbia\\Extensions\\Mvc\\controllers::load', $uArgs);
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
        call_user_func_array('Scabbia\\Extensions\\Mvc\\mvc::redirect', $uArgs);
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
