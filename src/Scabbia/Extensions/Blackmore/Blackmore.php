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
use Scabbia\Extensions\Validation\Validation;
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
    public function render($uAction, $uParams, $uInput)
    {
        self::$modules[self::DEFAULT_MODULE_INDEX] = array(
            'title' => 'Blackmore',
            'actions' => array(
                self::DEFAULT_ACTION_INDEX => array(
                    'icon' => 'th-large',
                    'callback' => array(&$this, 'index'),
                    'menutitle' => 'Dashboard'
                )
            )
        );
        self::$modules[self::LOGIN_MODULE_INDEX] = array(
            'actions' => array(
                self::DEFAULT_ACTION_INDEX => array(
                    'icon' => 'remove-circle',
                    'callback' => array(&$this, 'login')
                )
            )
        );

        $tParms = array(
            'modules' => &self::$modules
        );
        Events::invoke('blackmoreRegisterModules', $tParms);

        self::$module = (strlen($uAction) > 0) ? strtolower($uAction) : self::DEFAULT_MODULE_INDEX;
        if (!isset(self::$modules[self::$module])) {
            return false;
        }

        foreach (self::$modules as $tModuleKey => $tModule) {
            if (!isset($tModule['title'])) {
                continue;
            }

            self::$menuItems[$tModuleKey] = array(
                ($tModuleKey == self::DEFAULT_MODULE_INDEX) ? Http::url('blackmore') : Http::url('blackmore/' . $tModuleKey),
                _($tModule['title']),
                array()
            );

            foreach ($tModule['actions'] as $tActionKey => $tAction) {
                if (is_null($tAction)) {
                    self::$menuItems[$tModuleKey][2][] = '-';
                    continue;
                }

                if (!isset($tAction['menutitle'])) {
                    continue;
                }

                if (isset($tAction['customurl'])) {
                    $tUrl = $tAction['customurl'];
                } elseif ($tActionKey === self::DEFAULT_ACTION_INDEX) {
                    if ($tModuleKey === self::DEFAULT_MODULE_INDEX) {
                        $tUrl = Http::url('blackmore');
                    } else {
                        $tUrl = Http::url('blackmore/' . $tModuleKey);
                    }
                } else {
                    $tUrl = Http::url('blackmore/' . $tModuleKey . '/' . $tActionKey);
                }

                self::$menuItems[$tModuleKey][2][] = array(
                    $tUrl,
                    isset($tAction['icon']) ? $tAction['icon'] : 'minus',
                    _($tAction['menutitle'])
                );
            }
        }
        self::$menuItems[self::DEFAULT_MODULE_INDEX][2][] = '-';
        self::$menuItems[self::DEFAULT_MODULE_INDEX][2][] = array(
            Http::url('blackmore/' . self::LOGIN_MODULE_INDEX),
            'remove-circle',
            _('Logout')
        );

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
        Validation::addRule('password')->lengthMinimum(4)->errorMessage('Password should be longer than 4 characters at least.');

        if (!Validation::validate($_POST)) {
            $this->set('error', implode('<br />', Validation::getErrorMessages(true)));
            $this->viewFile('{core}views/blackmore/login.php');

            return;
        }

        $username = Request::post('username');
        $password = Request::post('password');

        // user not found
        if (!Auth::login($username, $password)) {
            $this->set('error', 'User not found');
            $this->viewFile('{core}views/blackmore/login.php');

            return;
        }

        $this->redirect('blackmore');
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
