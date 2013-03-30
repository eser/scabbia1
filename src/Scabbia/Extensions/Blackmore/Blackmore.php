<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Blackmore;

use Scabbia\Extensions\Auth\Auth;
use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Mvc\Controller;
use Scabbia\Extensions\Session\Session;
use Scabbia\Extensions\Validation\Validation;
use Scabbia\Config;
use Scabbia\Events;

/**
 * Blackmore Extension
 *
 * @package Scabbia
 * @subpackage Blackmore
 * @version 1.1.0
 *
 * @todo refactor using subcontrollers
 */
class Blackmore extends Controller
{
    /**
     * @ignore
     */
    const DEFAULT_MODULE_INDEX = 'index';
    /**
     * @ignore
     */
    const LOGIN_MODULE_INDEX = 'login';
    /**
     * @ignore
     */
    const DEFAULT_ACTION_INDEX = 'index';


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
    public function render($uAction, array $uParams, array $uInput)
    {
        self::$modules[self::DEFAULT_MODULE_INDEX] = array(
            'title' => 'Blackmore',
            'actions' => array(
                self::DEFAULT_ACTION_INDEX => array(
                    'callback' => array(&$this, 'index')
                )
            )
        );
        self::$modules[self::LOGIN_MODULE_INDEX] = array(
            'actions' => array(
                self::DEFAULT_ACTION_INDEX => array(
                    'callback' => array(&$this, 'login')
                )
            )
        );

        $tParms = array(
            'modules' => &self::$modules
        );
        Events::invoke('registerBlackmoreModules', $tParms);

        self::$module = (strlen($uAction) > 0) ? strtolower($uAction) : self::DEFAULT_MODULE_INDEX;
        if (!isset(self::$modules[self::$module])) {
            return false;
        }

        foreach (Config::get('blackmore/menuList', array()) as $tKey => $tMenu) {
            self::$menuItems[$tKey] = array(
                ($tKey == self::DEFAULT_MODULE_INDEX) ? Http::url('blackmore') : Http::url('blackmore/' . $tKey),
                _($tMenu['title']),
                array()
            );

            foreach ($tMenu['actions'] as $tMenuActionKey => $tMenuAction) {
                if (isset($tMenuAction['before'])) {
                    if ($tMenuAction['before'] == 'separator') {
                        self::$menuItems[$tKey][2][] = '-';
                    }
                }

                if (isset($tMenuAction['customurl'])) {
                    $tUrl = $tMenuAction['customurl'];
                } elseif ($tMenuActionKey === self::DEFAULT_ACTION_INDEX) {
                    if ($tKey === self::DEFAULT_MODULE_INDEX) {
                        $tUrl = Http::url('blackmore');
                    } else {
                        $tUrl = Http::url('blackmore/' . $tKey);
                    }
                } else {
                    $tUrl = Http::url('blackmore/' . $tKey . '/' . $tMenuActionKey);
                }

                self::$menuItems[$tKey][2][] = array(
                    $tUrl,
                    isset($tMenuAction['icon']) ? $tMenuAction['icon'] : 'minus',
                    _($tMenuAction['title'])
                );

                if (isset($tMenuAction['after'])) {
                    if ($tMenuAction['after'] == 'separator') {
                        self::$menuItems[$tKey][2][] = '-';
                    }
                }
            }
        }

        $tSubAction = (count($uParams) > 0) ? $uParams[0] : self::DEFAULT_ACTION_INDEX;
        return call_user_func_array(self::$modules[self::$module]['actions'][$tSubAction]['callback'], $uParams);
    }

    /**
     * @ignore
     */
    public function login()
    {
        if (Request::$method != 'post') {
            Auth::clear();

            $this->viewFile('{core}views/blackmore/login.php');

            return;
        }

        // validations
        Validation::addRule('username')->isRequired()->errorMessage('Username shouldn\'t be blank.');
        // Validation::addRule('username')->isEmail()->errorMessage('Please consider your e-mail address once again.');
        Validation::addRule('password')->isRequired()->errorMessage('Password shouldn\'t be blank.');
        Validation::addRule('password')
            ->lengthMinimum(4)
            ->errorMessage('Password should be longer than 4 characters at least.');

        if (!Validation::validate($_POST)) {
            Session::set(
                'notification',
                array('error', 'remove-sign', Validation::getErrorMessages(true))
            );
            $this->viewFile('{core}views/blackmore/login.php');

            return;
        }

        $username = Request::post('username');
        $password = Request::post('password');

        // user not found
        if (!Auth::login($username, $password)) {
            Session::set('notification', array('error', 'remove-sign', 'User not found'));
            $this->viewFile('{core}views/blackmore/login.php');

            return;
        }

        Http::redirect('blackmore');
    }

    /**
     * @ignore
     */
    public function index()
    {
        Auth::checkRedirect('user');

        $this->viewFile('{core}views/blackmore/index.php');
    }
}
