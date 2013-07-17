<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Panel\Controllers;

use Scabbia\Extensions\Auth\Auth;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\I18n\I18n;
use Scabbia\Extensions\Mvc\Controller;
use Scabbia\Extensions\Session\Session;
use Scabbia\Extensions\Validation\Validation;
use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Framework;
use Scabbia\Request;

/**
 * Panel Extension
 *
 * @package Scabbia
 * @subpackage Panel
 * @version 1.1.0
 *
 * @todo refactor using subcontrollers
 */
class Panel extends Controller
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
    const MENU_TITLEURL = 0;
    /**
     * @ignore
     */
    const MENU_TITLE = 1;
    /**
     * @ignore
     */
    const MENU_ITEMS = 2;
    /**
     * @ignore
     */
    const MENUITEM_URL = 0;
    /**
     * @ignore
     */
    const MENUITEM_ICON = 1;
    /**
     * @ignore
     */
    const MENUITEM_TITLE = 2;


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
        I18n::setLanguage('en');

        self::$modules = Config::get('panel/menuList', array());

        self::$modules[self::LOGIN_MODULE_INDEX] = array(
            'actions' => array(
                self::DEFAULT_ACTION_INDEX => array(
                    'callback' => array(&$this, 'login')
                )
            )
        );

        foreach (Config::get('panel/menuGeneratorList', array()) as $tGeneratorMethod) {
            call_user_func_array($tGeneratorMethod, array(&self::$modules));
        }

        self::$module = (strlen($uAction) > 0) ? strtolower($uAction) : self::DEFAULT_MODULE_INDEX;
        if (!isset(self::$modules[self::$module])) {
            return false;
        }

        foreach (self::$modules as $tKey => $tMenu) {
            if (!isset($tMenu['title']) ||
                (isset($tMenu['role']) && !Auth::check($tMenu['role']))) {
                continue;
            }

            self::$menuItems[$tKey] = array(
                ($tKey === self::DEFAULT_MODULE_INDEX) ? Http::url('panel') : Http::url('panel/' . $tKey),
                I18n::_($tMenu['title']),
                array()
            );

            foreach ($tMenu['actions'] as $tMenuActionKey => $tMenuAction) {
                if (!isset($tMenuAction['title']) ||
                    (isset($tMenuAction['role']) && !Auth::check($tMenuAction['role']))) {
                    continue;
                }

                if (isset($tMenuAction['before'])) {
                    if ($tMenuAction['before'] === 'separator') {
                        self::$menuItems[$tKey][self::MENU_ITEMS][] = '-';
                    }
                }

                if (isset($tMenuAction['customurl'])) {
                    $tUrl = $tMenuAction['customurl'];
                } elseif ($tMenuActionKey === self::DEFAULT_ACTION_INDEX) {
                    if ($tKey === self::DEFAULT_MODULE_INDEX) {
                        $tUrl = Http::url('panel');
                    } else {
                        $tUrl = Http::url('panel/' . $tKey);
                    }
                } else {
                    $tUrl = Http::url('panel/' . $tKey . '/' . $tMenuActionKey);
                }

                self::$menuItems[$tKey][self::MENU_ITEMS][] = array(
                    $tUrl,
                    isset($tMenuAction['icon']) ? $tMenuAction['icon'] : 'minus',
                    I18n::_($tMenuAction['title'])
                );

                if (isset($tMenuAction['after'])) {
                    if ($tMenuAction['after'] === 'separator') {
                        self::$menuItems[$tKey][self::MENU_ITEMS][] = '-';
                    }
                }
            }
        }

        $tSubAction = (count($uParams) > 0) ? $uParams[0] : self::DEFAULT_ACTION_INDEX;

        if (!isset(self::$modules[self::$module]['actions'][$tSubAction])) {
            return false;
        }

        return call_user_func_array(self::$modules[self::$module]['actions'][$tSubAction]['callback'], $uParams);
    }

    /**
     * @ignore
     */
    public function login()
    {
        if (Request::$method !== 'post') {
            Auth::clear();

            $this->viewFile('{core}views/panel/login.php');

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
            $this->viewFile('{core}views/panel/login.php');

            return;
        }

        $username = Request::post('username');
        $password = Request::post('password');

        // user not found
        if (!Auth::login($username, $password)) {
            Session::set('notification', array('error', 'remove-sign', 'User not found'));
            $this->viewFile('{core}views/panel/login.php');

            return;
        }

        Http::redirect('panel');
    }

    /**
     * @ignore
     */
    public function index()
    {
        Auth::checkRedirect('user');

        $this->viewFile('{core}views/panel/index.php');
    }
}
