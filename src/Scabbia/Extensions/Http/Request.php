<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Http;

use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Http\Router;
use Scabbia\Extensions\Objects\Collection;
use Scabbia\Config;
use Scabbia\Utils;

/**
 * Http Extension: Request Class
 *
 * @package Scabbia
 * @subpackage Http
 * @version 1.1.0
 */
class Request
{
    /**
     * @ignore
     */
    public static $platform = null;
    /**
     * @ignore
     */
    public static $crawler = null;
    /**
     * @ignore
     */
    public static $crawlerType = null;
    /**
     * @ignore
     */
    public static $isAjax = false;
    /**
     * @ignore
     */
    public static $wrapperFunction = false;
    /**
     * @ignore
     */
    public static $queryString;
    /**
     * @ignore
     */
    public static $get;
    /**
     * @ignore
     */
    public static $post;
    /**
     * @ignore
     */
    public static $cookie;
    /**
     * @ignore
     */
    public static $route;
    /**
     * @ignore
     */
    public static $remoteIp;
    /**
     * @ignore
     */
    public static $https;
    /**
     * @ignore
     */
    public static $host;
    /**
     * @ignore
     */
    public static $protocol;
    /**
     * @ignore
     */
    public static $method;
    /**
     * @ignore
     */
    public static $methodext;
    /**
     * @ignore
     */
    public static $isBrowser = false;
    /**
     * @ignore
     */
    public static $isRobot = false;
    /**
     * @ignore
     */
    public static $isMobile = false;
    /**
     * @ignore
     */
    public static $languages = array();
    /**
     * @ignore
     */
    public static $contentTypes = array();


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        // $remoteIp
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            self::$remoteIp = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!isset($_SERVER['REMOTE_ADDR']) && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            self::$remoteIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            self::$remoteIp = $_SERVER['REMOTE_ADDR'] = getenv('REMOTE_ADDR') or self::$remoteIp = '0.0.0.0';
        }

        // $https
        self::$https = (
            isset($_SERVER['HTTPS']) && (
                $_SERVER['HTTPS'] == '1' ||
                strcasecmp($_SERVER['HTTPS'], 'on') == 0
            )
        );

        // $protocol
        if (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0') {
            self::$protocol = 'HTTP/1.0';
        } else {
            self::$protocol = 'HTTP/1.1';
        }

        // $host
        if (isset($_SERVER['HTTP_HOST']) && strlen($_SERVER['HTTP_HOST']) > 0) {
            self::$host = $_SERVER['HTTP_HOST'];
        } else {
            self::$host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR'];

            if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != '80') {
                self::$host .= $_SERVER['SERVER_PORT'];
            }
        }

        // $method, $methodext, $isAjax
        self::$method = strtolower($_SERVER['REQUEST_METHOD']);
        self::$methodext = self::$method;

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            self::$isAjax = true;
            self::$methodext .= 'ajax';
        }

        if (isset($_SERVER['HTTP_X_WRAPPER_FUNCTION'])) {
            self::$wrapperFunction = $_SERVER['HTTP_X_WRAPPER_FUNCTION'];
        }

        // get/post/cookie
        self::$get = new Collection($_GET);
        self::$post = new Collection($_POST);
        self::$cookie = new Collection($_COOKIE);

        // $userAgent
        if (Config::get('http/userAgents/autoCheck', false) !== false) {
            self::checkUserAgent();
        }

        // self::$browser = get_browser(null, true);

        // $languages, $contentTypes
        self::$languages = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ?
            Http::parseHeaderString($_SERVER['HTTP_ACCEPT_LANGUAGE'], true) :
            array();
        self::$contentTypes = isset($_SERVER['HTTP_ACCEPT']) ?
            Http::parseHeaderString($_SERVER['HTTP_ACCEPT'], true) :
            array();

        // $queryString
        self::$queryString = $_SERVER['QUERY_STRING'];
        self::$route = Router::resolve(self::$queryString, self::$methodext);
    }

    /**
     * @ignore
     */
    public static function rewriteUrl(&$uUrl, $uMatch, $uForward)
    {
        $tReturn = Utils::pregReplace($uMatch, $uForward, $uUrl);
        if ($tReturn !== false) {
            $uUrl = $tReturn;

            return true;
        }

        return false;
    }

    /**
     * @ignore
     */
    public static function checkUserAgent()
    {
        foreach (Config::get('http/userAgents/platformList', array()) as $tPlatformList) {
            if (preg_match('/' . $tPlatformList['match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
                self::$platform = $tPlatformList['name'];
                break;
            }
        }

        foreach (Config::get('http/userAgents/crawlerList', array()) as $tCrawlerList) {
            if (preg_match('/' . $tCrawlerList['match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
                self::$crawler = $tCrawlerList['name'];
                self::$crawlerType = $tCrawlerList['type'];

                switch ($tCrawlerList['type']) {
                    case 'bot':
                        self::$isRobot = true;
                        break;
                    case 'mobile':
                        self::$isMobile = true;
                        break;
                    case 'browser':
                    default:
                        self::$isBrowser = true;
                        break;
                }

                break;
            }
        }
    }

    /**
     * @ignore
     */
    public static function checkLanguage($uLanguage = null)
    {
        if (is_null($uLanguage)) {
            return self::$languages;
        }

        return in_array(strtolower($uLanguage), self::$languages);
    }

    /**
     * @ignore
     */
    public static function checkContentType($uContentType = null)
    {
        if (is_null($uContentType)) {
            return self::$contentTypes;
        }

        return in_array(strtolower($uContentType), self::$contentTypes);
    }

    /**
     * @ignore
     */
    public static function get($uKey, $uDefault = null, $uFilter = String::FILTER_SANITIZE_XSS)
    {
        if (!isset($_GET[$uKey])) {
            return $uDefault;
        }

        if (is_null($uFilter)) {
            return $_GET[$uKey];
        }

        if (func_num_args() > 2) {
            $tNewArgs = array_slice(func_get_args(), 2);
            array_unshift($tNewArgs, $_GET[$uKey]);
        } else {
            $tNewArgs = array($_GET[$uKey], $uFilter);
        }

        return call_user_func_array('Scabbia\\Extensions\\Helpers\\String::filter', $tNewArgs);
    }

    /**
     * @ignore
     */
    public static function post($uKey, $uDefault = null, $uFilter = String::FILTER_SANITIZE_XSS)
    {
        if (!isset($_POST[$uKey])) {
            return $uDefault;
        }

        if (is_null($uFilter)) {
            return $_POST[$uKey];
        }

        if (func_num_args() > 2) {
            $tNewArgs = array_slice(func_get_args(), 2);
            array_unshift($tNewArgs, $_POST[$uKey]);
        } else {
            $tNewArgs = array($_POST[$uKey], $uFilter);
        }

        return call_user_func_array('Scabbia\\Extensions\\Helpers\\String::filter', $tNewArgs);
    }

    /**
     * @ignore
     */
    public static function cookie($uKey, $uDefault = null, $uFilter = String::FILTER_SANITIZE_XSS)
    {
        if (!isset($_COOKIE[$uKey])) {
            return $uDefault;
        }

        if (is_null($uFilter)) {
            return $_COOKIE[$uKey];
        }

        if (func_num_args() > 2) {
            $tNewArgs = array_slice(func_get_args(), 2);
            array_unshift($tNewArgs, $_COOKIE[$uKey]);
        } else {
            $tNewArgs = array($_COOKIE[$uKey], $uFilter);
        }

        return call_user_func_array('Scabbia\\Extensions\\Helpers\\String::filter', $tNewArgs);
    }


    /**
     * @ignore
     */
    public static function getArray($uKeys, $uFilter = String::FILTER_SANITIZE_XSS)
    {
        $tValues = array();
        if (!is_null($uFilter)) {
            if (func_num_args() > 1) {
                $tArgs = array_slice(func_get_args(), 1);
            } else {
                $tArgs = array($uFilter);
            }
        }

        foreach ((array)$uKeys as $tKey) {
            if (!isset($_GET[$tKey])) {
                continue;
            }

            if (!isset($tArgs)) {
                $tValues[$tKey] = $_GET[$tKey];
                continue;
            }

            $tNewArgs = $tArgs;
            array_unshift($tNewArgs, $_GET[$tKey]);

            $tValues[$tKey] = call_user_func_array('Scabbia\\Extensions\\Helpers\\String::filter', $tNewArgs);
        }

        return $tValues;
    }

    /**
     * @ignore
     */
    public static function postArray($uKeys, $uFilter = String::FILTER_SANITIZE_XSS)
    {
        $tValues = array();
        if (!is_null($uFilter)) {
            if (func_num_args() > 1) {
                $tArgs = array_slice(func_get_args(), 1);
            } else {
                $tArgs = array($uFilter);
            }
        }

        foreach ((array)$uKeys as $tKey) {
            if (!isset($_POST[$tKey])) {
                continue;
            }

            if (!isset($tArgs)) {
                $tValues[$tKey] = $_POST[$tKey];
                continue;
            }

            $tNewArgs = $tArgs;
            array_unshift($tNewArgs, $_POST[$tKey]);

            $tValues[$tKey] = call_user_func_array('Scabbia\\Extensions\\Helpers\\String::filter', $tNewArgs);
        }

        return $tValues;
    }

    /**
     * @ignore
     */
    public static function cookieArray($uKeys, $uFilter = String::FILTER_SANITIZE_XSS)
    {
        $tValues = array();
        if (!is_null($uFilter)) {
            if (func_num_args() > 1) {
                $tArgs = array_slice(func_get_args(), 1);
            } else {
                $tArgs = array($uFilter);
            }
        }

        foreach ((array)$uKeys as $tKey) {
            if (!isset($_COOKIE[$tKey])) {
                continue;
            }

            if (!isset($tArgs)) {
                $tValues[$tKey] = $_COOKIE[$tKey];
                continue;
            }

            $tNewArgs = $tArgs;
            array_unshift($tNewArgs, $_COOKIE[$tKey]);

            $tValues[$tKey] = call_user_func_array('Scabbia\\Extensions\\Helpers\\String::filter', $tNewArgs);
        }

        return $tValues;
    }
}
