<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Session;

use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Helpers\String;
use Scabbia\Config;

/**
 * Session Extension
 *
 * @package Scabbia
 * @subpackage Session
 * @version 1.1.0
 *
 * @todo Cookie salt w/ SHA1
 * @todo garbage collector
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

        if (self::$id !== null) {
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

        if (self::$id === null) {
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
        if (self::$data === null) { // !== null
            self::open();
        }

        if (self::$id === null) {
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
        if (self::$data === null) {
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
        if (self::$data === null) {
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
        if (self::$data === null) {
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
        if (self::$data === null) {
            self::open();
        }

        return isset(self::$data[$uKey]);
    }

    /**
     * @ignore
     */
    public static function getKeys()
    {
        if (self::$data === null) {
            self::open();
        }

        return array_keys(self::$data);
    }

    /**
     * @ignore
     */
    public static function getFlash($uKey, $uDefault = null)
    {
        if (self::$data === null) {
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
        if (self::$data === null) {
            self::open();
        }

        return String::vardump(self::$data, $tOutput);
    }
}
