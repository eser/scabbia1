<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Auth;

use Scabbia\Config;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Session\Session;
use Scabbia\Framework;

/**
 * Auth Extension
 *
 * @package Scabbia
 * @subpackage Auth
 * @version 1.1.0
 *
 * @todo datasources integration
 */
class Auth
{
    /**
     * @var string Session key will be stored by client
     */
    public static $sessionKey = null;


    /**
     * Lazy loading method for the extension
     */
    public static function load()
    {
        if (is_null(self::$sessionKey)) {
            self::$sessionKey = Config::get('auth/sessionKey', 'authuser');
        }
    }

    /**
     * Allows authenticated users to log into the system
     *
     * @param string $uUsername username
     * @param string $uPassword password
     *
     * @return bool whether the user logged in or not
     */
    public static function login($uUsername, $uPassword)
    {
        self::load();

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
     * Clears logged user information
     */
    public static function clear()
    {
        self::load();

        Session::remove(self::$sessionKey);
    }

    /**
     * Checks if the logged user has the specific roles
     *
     * @param string $uRequiredRoles roles
     *
     * @return bool whether logged user has the role or not
     */
    public static function check($uRequiredRoles = 'user')
    {
        self::load();

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
     * Redirects users to another location if user does not have required roles
     *
     * @uses Auth::check($uRequiredRoles)
     * @param string $uRequiredRoles roles
     */
    public static function checkRedirect($uRequiredRoles = 'user')
    {
        self::load();

        if (self::check($uRequiredRoles)) {
            return;
        }

        $tMvcUrl = Config::get('auth/loginMvcUrl', null);
        if (!is_null($tMvcUrl)) {
            //! todo: warning messages like insufficent privileges.
            Http::redirect($tMvcUrl, true);
        }

        header('Location: ' . Config::get('auth/loginUrl'));
        Framework::end(0);
    }
}
