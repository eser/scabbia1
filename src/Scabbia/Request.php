<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Extensions\Http\Router;
use Scabbia\Config;
use Scabbia\Framework;

/**
 * Http Extension: Request Class
 *
 * @package Scabbia
 * @subpackage Http
 * @version 1.1.0
 *
 * @todo ->file()
 */
class Request
{
    /**
     * @ignore
     */
    const FILTER_VALIDATE_BOOLEAN = 'scabbiaFilterValidateBoolean';
    /**
     * @ignore
     */
    const FILTER_SANITIZE_BOOLEAN = 'scabbiaFilterSanitizeBoolean';
    /**
     * @ignore
     */
    const FILTER_SANITIZE_XSS = 'scabbiaFilterSanitizeXss';


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
    public static $route;
    /**
     * @ignore
     */
    public static $remoteIp;
    /**
     * @ignore
     */
    public static $siteroot;
    /**
     * @ignore
     */
    public static $https;
    /**
     * @ignore
     */
    public static $protocol;
    /**
     * @ignore
     */
    public static $host;
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
    public static $languages = array();
    /**
     * @ignore
     */
    public static $contentTypes = array();


    /**
     * @ignore
     */
    public static function init()
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
        if (PHP_SAPI == 'cli') {
            self::$protocol = 'CLI';
        } elseif (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'HTTP/1.0') {
            self::$protocol = 'HTTP/1.0';
        } else {
            self::$protocol = 'HTTP/1.1';
        }

        // $host
        if (isset($_SERVER['HTTP_HOST']) && strlen($_SERVER['HTTP_HOST']) > 0) {
            self::$host = $_SERVER['HTTP_HOST'];
        } else {
            self::$host = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['SERVER_ADDR'];

            if (isset($_SERVER['SERVER_PORT'])) {
                if (self::$https) {
                    if ($_SERVER['SERVER_PORT'] != '443') {
                        self::$host .= $_SERVER['SERVER_PORT'];
                    }
                } else {
                    if ($_SERVER['SERVER_PORT'] != '80') {
                        self::$host .= $_SERVER['SERVER_PORT'];
                    }
                }
            }
        }

        // $method, $isAjax, $wrapperFunction, etc.
        self::$method = strtolower($_SERVER['REQUEST_METHOD']);
        self::$methodext = self::$method;

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            self::$isAjax = true;
            self::$methodext .= 'ajax';
        }

        if (isset($_SERVER['HTTP_X_WRAPPER_FUNCTION'])) {
            self::$wrapperFunction = $_SERVER['HTTP_X_WRAPPER_FUNCTION'];
        }

        // $languages, $contentTypes
        self::$languages = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ?
            self::parseHeaderString($_SERVER['HTTP_ACCEPT_LANGUAGE'], true) :
            array();
        self::$contentTypes = isset($_SERVER['HTTP_ACCEPT']) ?
            self::parseHeaderString($_SERVER['HTTP_ACCEPT'], true) :
            array();

        // $queryString
        self::$queryString = $_SERVER['QUERY_STRING'];
        self::$route = Router::resolve(self::$queryString, self::$methodext);

        // framework variables
        self::$siteroot = trim(Config::get('options/siteroot', pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME)), '/');
        if (strlen(self::$siteroot) > 0) {
            self::$siteroot = '/' . self::$siteroot;
        }

        Framework::$variables['root'] = self::$siteroot;
        Framework::$variables['host'] = self::$host;
        Framework::$variables['scheme'] = self::$https ? 'https' : 'http';
        Framework::$variables['method'] = self::$method;
    }

    /**
     * @ignore
     */
    public static function parseHeaderString($uString, $uLowerAll = false)
    {
        $tResult = array();

        foreach (explode(',', $uString) as $tPiece) {
            // pull out the language, place languages into array of full and primary
            // string structure:
            $tPiece = trim($tPiece);
            if ($uLowerAll) {
                $tResult[] = strtolower(substr($tPiece, 0, strcspn($tPiece, ';')));
            } else {
                $tResult[] = substr($tPiece, 0, strcspn($tPiece, ';'));
            }
        }

        return $tResult;
    }

    /**
     * @ignore
     *
     * @todo recursive filtering option
     */
    public static function filter($uValue, $uFilter)
    {
        if ($uFilter == self::FILTER_VALIDATE_BOOLEAN) {
            if (
                $uValue === true || $uValue == 'true' || $uValue === 1 || $uValue == '1' ||
                $uValue === false || $uValue == 'false' || $uValue === 0 || $uValue == '0'
            ) {
                return true;
            }

            return false;
        }

        if ($uFilter == self::FILTER_SANITIZE_BOOLEAN) {
            if ($uValue === true || $uValue == 'true' || $uValue === 1 || $uValue == '1') {
                return true;
            }

            return false;
        }

        if ($uFilter == self::FILTER_SANITIZE_XSS) {
            return self::xss($uValue);
        }

        $uArgs = func_get_args();

        if (is_callable($uFilter, true)) {
            $uArgs[1] = $uValue;
            return call_user_func_array($uFilter, array_slice($uArgs, 1));
        }

        return call_user_func_array('filter_var', $uArgs);
    }

    /**
     * @ignore
     */
    public static function xss($uString)
    {
        if (!is_string($uString)) {
            return $uString;
        }

        return str_replace(
            array(
                '<',
                '>',
                '"',
                '\'',
                '$',
                '(',
                ')',
                '%28',
                '%29'
            ),
            array(
                '&#60;',
                '&#62;',
                '&#34;',
                '&#39;',
                '&#36;',
                '&#40;',
                '&#41;',
                '&#40;',
                '&#41;'
            ),
            $uString
        ); // '&' => '&#38;'
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
    public static function get($uKey, $uDefault = null, $uFilter = self::FILTER_SANITIZE_XSS)
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

        return call_user_func_array('Scabbia\\Request::filter', $tNewArgs);
    }

    /**
     * @ignore
     */
    public static function post($uKey, $uDefault = null, $uFilter = self::FILTER_SANITIZE_XSS)
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

        return call_user_func_array('Scabbia\\Request::filter', $tNewArgs);
    }

    /**
     * @ignore
     */
    public static function cookie($uKey, $uDefault = null, $uFilter = self::FILTER_SANITIZE_XSS)
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

        return call_user_func_array('Scabbia\\Request::filter', $tNewArgs);
    }


    /**
     * @ignore
     */
    public static function getArray($uKeys, $uFilter = self::FILTER_SANITIZE_XSS)
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

            $tValues[$tKey] = call_user_func_array('Scabbia\\Request::filter', $tNewArgs);
        }

        return $tValues;
    }

    /**
     * @ignore
     */
    public static function postArray($uKeys, $uFilter = self::FILTER_SANITIZE_XSS)
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

            $tValues[$tKey] = call_user_func_array('Scabbia\\Request::filter', $tNewArgs);
        }

        return $tValues;
    }

    /**
     * @ignore
     */
    public static function cookieArray($uKeys, $uFilter = self::FILTER_SANITIZE_XSS)
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

            $tValues[$tKey] = call_user_func_array('Scabbia\\Request::filter', $tNewArgs);
        }

        return $tValues;
    }
}
