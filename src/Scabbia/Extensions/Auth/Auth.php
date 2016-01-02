<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Auth;

use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Session\Session;
use Scabbia\Config;
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
     * @var string Authorization type
     */
    public static $type = null;
    /**
     * @var string Password hash algorithm
     */
    public static $hash = null;
    /**
     * @var string Session key will be stored by client
     */
    public static $sessionKey = null;
    /**
     * @var string Default roles
     */
    public static $defaultRoles = null;
    /**
     * @var array Current user information
     */
    public static $user = null;


    /**
     * Lazy loading method for the extension
     */
    public static function load()
    {
        if (self::$type === null) {
            self::$type = Config::get('auth/authType', 'config');
            self::$hash = Config::get('auth/authHash', 'md5');
            self::$sessionKey = Config::get('auth/sessionKey', 'authuser');
            self::$defaultRoles = Config::get('auth/defaultRoles', 'user');

            self::$user = Session::get(self::$sessionKey, null);
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

        if (self::$hash === 'md5') {
            $tPassword = md5($uPassword);
        } else {
            $tPassword = $uPassword;
        }

        if (self::$type === 'config') {
            foreach (Config::get('auth/userList', array()) as $tUser) {
                if ($uUsername !== $tUser['username'] || $tPassword !== $tUser['password']) {
                    continue;
                }

                Session::set(
                    self::$sessionKey,
                    array(
                        'username' => $tUser['username'],
                        'roles' => isset(self::$user['roles']) ? $tUser['roles'] : self::$defaultRoles,
                        'extra' => $tUser
                    )
                );

                return true;
            }
        } elseif (self::$type === 'database') {
            $tDatasource = Config::get('auth/database/datasource');
            $tQuery = Config::get('auth/database/query');
            $tPasswordField = Config::get('auth/database/passwordField');

            $tDbConn = Datasources::get($tDatasource);
            $tResult = $tDbConn->query(
                $tQuery,
                array(
                    'username' => $uUsername
                )
            )->row();

            if ($tResult !== false && isset($tResult[$tPasswordField]) &&
                $tResult[$tPasswordField] === $tPassword) {

                Session::set(
                    self::$sessionKey,
                    array(
                        'username' => $uUsername,
                        'roles' => isset(self::$user['roles']) ? $tResult['roles'] : self::$defaultRoles,
                        'extra' => $tResult
                    )
                );

                return true;
            }
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

        if (self::$user === null) {
            return false;
        }

        $tAvailableRoles = explode(',', self::$user['roles']);

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

        $tLoginUrl = Config::get('auth/loginUrl', null);
        if ($tLoginUrl !== null) {
            //! todo: warning messages like insufficent privileges.
            Http::redirect($tLoginUrl, true);
        }

        Framework::end(0);
    }
}
