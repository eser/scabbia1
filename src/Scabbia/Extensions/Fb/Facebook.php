<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Fb;

use Scabbia\Extensions\Session\Session;

/**
 * Fb Extension: Facebook Class
 *
 * Extends the BaseFacebook class with the intent of using
 * PHP sessions to store user ids and access tokens.
 *
 * @package Scabbia
 * @subpackage Fb
 * @version 1.1.0
 */
class Facebook extends \BaseFacebook
{
    /**
     * Identical to the parent constructor, except that
     * we start a PHP session to store the user ID and
     * access token if during the course of execution
     * we discover them.
     *
     * @param Array $config the application configuration.
     *
     * @see BaseFacebook::__construct in facebook.php
     */
    public function __construct($config)
    {
        parent::__construct($config);
    }

    protected static $kSupportedKeys = array('state', 'code', 'access_token', 'user_id');

    /**
     * Provides the implementations of the inherited abstract
     * methods.  The implementation uses PHP sessions to maintain
     * a store for authorization codes, user ids, CSRF states, and
     * access tokens.
     */
    protected function setPersistentData($key, $value)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to setPersistentData.');

            return;
        }

        $session_var_name = $this->constructSessionVariableName($key);

        Session::set($session_var_name, $value);
    }

    protected function getPersistentData($key, $default = false)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to getPersistentData.');

            return $default;
        }

        $session_var_name = $this->constructSessionVariableName($key);

        return Session::get($session_var_name, $default);
    }

    protected function clearPersistentData($key)
    {
        if (!in_array($key, self::$kSupportedKeys)) {
            self::errorLog('Unsupported key passed to clearPersistentData.');

            return;
        }

        $session_var_name = $this->constructSessionVariableName($key);
        Session::remove($session_var_name);
    }

    protected function clearAllPersistentData()
    {
        foreach (self::$kSupportedKeys as $key) {
            $this->clearPersistentData($key);
        }
    }

    protected function constructSessionVariableName($key)
    {
        return implode('_', array('fb', $this->getAppId(), $key));
    }

    public function unboxOauthRequest($url, $params)
    {
        return $this->_oauthRequest($url, $params);
    }

    public function unboxGetUrl($name, $path = '', $params = array())
    {
        return $this->getUrl($name, $path, $params);
    }
}
