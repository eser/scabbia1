<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Session;

use Scabbia\Extensions\Cache\Cache;
use Scabbia\Extensions\String\String;
use Scabbia\Config;
use Scabbia\Extensions;

/**
 * Session Extension
 *
 * @package Scabbia
 * @subpackage Session
 * @version 1.1.0
 *
 * @todo integrate with cache extension
 */
class Session
{
    /**
     * @ignore
     */
    public static $id = null;
    /**
     * @ignore
     */
    public static $data = null;
    /**
     * @ignore
     */
    public static $sessionName;
    /**
     * @ignore
     */
    public static $sessionLife;
    /**
     * @ignore
     */
    public static $isModified = false;


    /**
     * @ignore
     */
    public static function open()
    {
        self::$sessionName = Config::get('session/cookie/name', 'sessid');

        if (Config::get('session/cookie/nameIp', true)) {
            self::$sessionName .= hash('adler32', $_SERVER['REMOTE_ADDR']);
        }

        self::$sessionLife = intval(Config::get('session/cookie/life', '0'));

        if (array_key_exists(self::$sessionName, $_COOKIE)) {
            self::$id = $_COOKIE[self::$sessionName];
        }

        if (!is_null(self::$id)) {
            $tIpCheck = (bool)Config::get('session/cookie/ipCheck', '0');
            $tUACheck = (bool)Config::get('session/cookie/uaCheck', '1');

            $tData = Cache::fileGet('sessions/', self::$id, self::$sessionLife, true);
            if ($tData !== false) {
                if (
                    (!$tIpCheck || $tData['ip'] == $_SERVER['REMOTE_ADDR']) &&
                    (!$tUACheck || $tData['ua'] == $_SERVER['HTTP_USER_AGENT'])
                ) {
                    self::$data = $tData['data'];

                    return;
                }
            }
        }

        self::$data = array();
        self::$isModified = false;
    }

    /**
     * @ignore
     */
    public static function save()
    {
        if (!self::$isModified) {
            return;
        }

        if (is_null(self::$id)) {
            self::$id = String::generateUuid();
        }

        if (self::$sessionLife > 0) {
            $tCookieLife = time() + self::$sessionLife;
        } else {
            $tCookieLife = 0;
        }

        setcookie(self::$sessionName, self::$id, $tCookieLife, '/');

        $tData = array(
            'data' => self::$data,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'ua' => $_SERVER['HTTP_USER_AGENT']
        );

        Cache::fileSet('sessions/', self::$id, $tData);

        self::$isModified = false;
    }

    /**
     * @ignore
     */
    public static function destroy()
    {
        if (is_null(self::$data)) { // !is_null
            self::open();
        }

        if (is_null(self::$id)) {
            return;
        }

        setcookie(self::$sessionName, '', time() - 3600, '/');

        Cache::fileDestroy('sessions/', self::$id);

        self::$id = null;
        self::$data = null;

        self::$isModified = false;
    }

    /**
     * @ignore
     */
    public static function get($uKey, $uDefault = null)
    {
        if (is_null(self::$data)) {
            self::open();
        }

        if (!array_key_exists($uKey, self::$data)) {
            return $uDefault;
        }

        return self::$data[$uKey];
    }

    /**
     * @ignore
     */
    public static function set($uKey, $uValue)
    {
        if (is_null(self::$data)) {
            self::open();
        }

        self::$data[$uKey] = $uValue;
        self::$isModified = true;
    }

    /**
     * @ignore
     */
    public static function remove($uKey)
    {
        if (is_null(self::$data)) {
            self::open();
        }

        unset(self::$data[$uKey]);
        self::$isModified = true;
    }

    /**
     * @ignore
     */
    public static function exists($uKey)
    {
        if (is_null(self::$data)) {
            self::open();
        }

        return array_key_exists($uKey, self::$data);
    }

    /**
     * @ignore
     */
    public static function getKeys()
    {
        if (is_null(self::$data)) {
            self::open();
        }

        return array_keys(self::$data);
    }

    /**
     * @ignore
     */
    public static function getFlash($uKey, $uDefault = null)
    {
        if (is_null(self::$data)) {
            self::open();
        }

        if (array_key_exists($uKey, self::$data)) {
            $tValue = self::$data[$uKey];
            unset(self::$data[$uKey]);

            self::$isModified = true;

            return $tValue;
        }

        return $uDefault;
    }

    /**
     * @ignore
     */
    public static function export($tOutput = true)
    {
        if (is_null(self::$data)) {
            self::open();
        }

        return String::vardump(self::$data, $tOutput);
    }
}
