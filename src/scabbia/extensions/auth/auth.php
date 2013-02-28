<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Auth;

use Scabbia\Extensions\Mvc\Mvc;
use Scabbia\Extensions\Session\Session;
use Scabbia\Config;
use Scabbia\Extensions;
use Scabbia\Framework;

/**
 * Auth Extension
 *
 * @package Scabbia
 * @subpackage Auth
 * @version 1.1.0
 */
class Auth
{
    /**
     * @ignore
     */
    public static $sessionKey;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$sessionKey = Config::get('auth/sessionKey', 'authuser');
    }

    /**
     * @ignore
     */
    public static function login($uUsername, $uPassword)
    {
        foreach (Config::get('auth/userList', array()) as $tUser) {
            if ($uUsername != $tUser['username'] || md5($uPassword) != $tUser['password']) {
                continue;
            }

            Session::set(self::$sessionKey, $tUser);

            return true;
        }

        // Session::remove(self::$sessionKey);
        return false;
    }

    /**
     * @ignore
     */
    public static function clear()
    {
        Session::remove(self::$sessionKey);
    }

    /**
     * @ignore
     */
    public static function check($uRequiredRoles = 'user')
    {
        $tUser = Session::get(self::$sessionKey);
        if (is_null($tUser)) {
            return false;
        }

        $tAvailableRoles = explode(',', $tUser['roles']);

        foreach (explode(',', $uRequiredRoles) as $tRequiredRole) {
            if (!in_array($tRequiredRole, $tAvailableRoles, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function checkRedirect($uRequiredRoles = 'user')
    {
        if (self::check($uRequiredRoles)) {
            return;
        }

        $tMvcUrl = Config::get('auth/loginMvcUrl', null);
        if (!is_null($tMvcUrl)) {
            //! todo: warning messages like insufficent privileges.
            Http::redirect($tMvcUrl);
        } else {
            header('Location: ' . Config::get('auth/loginUrl'));
        }

        Framework::end(0);
    }
}
