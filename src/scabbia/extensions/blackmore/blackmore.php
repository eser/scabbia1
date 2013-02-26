<?php

namespace Scabbia\Extensions\Blackmore;

use Scabbia\Extensions\Auth\Auth;
use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Mvc\Controller;
use Scabbia\Extensions\Validation\Validation;
use Scabbia\Events;

/**
 * Blackmore Extension
 *
 * @package Scabbia
 * @subpackage blackmore
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends string, resources, validation, http, auth, zmodels
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
 */
class Blackmore extends Controller
{
    /**
     * @ignore
     */
    public static $menuItems = array();
    /**
     * @ignore
     */
    public static $modules = array();
    /**
     * @ignore
     */
    public static $module;


    /**
     * @ignore
     */
    public function render($uAction, $uParams, $uInput)
    {
        self::$modules['index'] = array(
            'title' => 'Dashboard',
            'callback' => array(&$this, 'index')
        );

        $tParms = array(
            'modules' => &self::$modules
        );
        Events::invoke('blackmoreRegisterModules', $tParms);

        self::$modules['login'] = array(
            'title' => 'Logout',
            'callback' => array(&$this, 'login')
        );

        if (!isset(self::$modules[$uAction])) {
            return false;
        }

        self::$module = $uAction;

        if (count($uParams) > 0) {
            foreach (self::$modules[$uAction]['actions'] as $tAction) {
                if ($uParams[0] != $tAction['action']) {
                    continue;
                }

                return call_user_func_array($tAction['callback'], $uParams);
            }
        }

        return call_user_func_array(self::$modules[$uAction]['callback'], $uParams);
    }

    /**
     * @ignore
     */
    public function login()
    {
        if (Request::$method != 'post') {
            Auth::clear();

            $this->viewFile('{vendor}views/blackmore/login.php');

            return;
        }

        // validations
        Validation::addRule('username')->isRequired()->errorMessage('Username shouldn\'t be blank.');
        // Validation::addRule('username')->isEmail()->errorMessage('Please consider your e-mail address once again.');
        Validation::addRule('password')->isRequired()->errorMessage('Password shouldn\'t be blank.');
        Validation::addRule('password')->lengthMinimum(4)->errorMessage('Password should be longer than 4 characters at least.');

        if (!Validation::validate($_POST)) {
            $this->set('error', implode('<br />', Validation::getErrorMessages(true)));
            $this->viewFile('{vendor}views/blackmore/login.php');

            return;
        }

        $username = Request::post('username');
        $password = Request::post('password');

        // user not found
        if (!Auth::login($username, $password)) {
            $this->set('error', 'User not found');
            $this->viewFile('{vendor}views/blackmore/login.php');

            return;
        }

        $this->redirect('blackmore/index');
    }

    /**
     * @ignore
     */
    public function index()
    {
        Auth::checkRedirect('user');

        $this->viewFile('{vendor}views/blackmore/index.php');
    }
}
