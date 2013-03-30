<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Session;

use Scabbia\Extensions\Datasources\Datasources;
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
 * @todo Cookie salt w/ SHA1
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
    public static $datasource;
    /**
     * @ignore
     */
    public static $sessionName;
    /**
     * @ignore
     */
    public static $sessionTtl;
    /**
     * @ignore
     */
    public static $isModified = false;


    /**
     * @ignore
     */
    public static function open()
    {
        self::$datasource = Datasources::get(Config::get('session/datasource', 'fileCache'));

        self::$sessionName = Config::get('session/cookie/name', 'sessid');

        if (Config::get('session/cookie/nameIp', true)) {
            self::$sessionName .= hash('adler32', $_SERVER['REMOTE_ADDR']);
        }

        self::$sessionTtl = Config::get('session/cookie/ttl', 0);

        if (isset($_COOKIE[self::$sessionName])) {
            self::$id = $_COOKIE[self::$sessionName];
        }

        if (!is_null(self::$id)) {
            $tIpCheck = Config::get('session/cookie/ipCheck', false);
            $tUACheck = Config::get('session/cookie/uaCheck', true);

            $tData = self::$datasource->cacheGet('sessions/' . self::$id);
            if ($tData !== false) {
                if ((!$tIpCheck || $tData['ip'] == $_SERVER['REMOTE_ADDR']) &&
                    (!$tUACheck || $tData['ua'] == $_SERVER['HTTP_USER_AGENT'])) {
                    self::$data = $tData['data'];

                    return;
                }
            }
        }

        self::$data = array();
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

        if (self::$sessionTtl > 0) {
            $tCookieTtl = time() + self::$sessionTtl;
        } else {
            $tCookieTtl = 0;
        }

        setcookie(self::$sessionName, self::$id, $tCookieTtl, '/');

        $tData = array(
            'data' => self::$data,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'ua' => $_SERVER['HTTP_USER_AGENT']
        );

        self::$datasource->cacheSet('sessions/' . self::$id, $tData);

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

        self::$datasource->cacheRemove('sessions/' . self::$id);

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

        if (!isset(self::$data[$uKey])) {
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

        return isset(self::$data[$uKey]);
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

        if (!isset(self::$data[$uKey])) {
            return $uDefault;
        }

        $tValue = self::$data[$uKey];
        unset(self::$data[$uKey]);

        self::$isModified = true;

        return $tValue;
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
