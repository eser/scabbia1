<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

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
    public static $requestUri;
    /**
     * @ignore
     */
    public static $pathInfo;
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
    public static $siteroot = null;
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
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            self::$remoteIp = $_SERVER['REMOTE_ADDR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            self::$remoteIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            self::$remoteIp = getenv('REMOTE_ADDR') or self::$remoteIp = '0.0.0.0';
        }

        // $https
        if (isset($_SERVER['HTTPS']) && ((string)$_SERVER['HTTPS'] === '1' || strcasecmp($_SERVER['HTTPS'], 'on') === 0)) {
            self::$https = true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            self::$https = true;
        } else {
            self::$https = false;
        }

        // $protocol
        if (PHP_SAPI === 'cli') {
            self::$protocol = 'CLI';
        } elseif (isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] === 'HTTP/1.0') {
            self::$protocol = 'HTTP/1.0';
        } else {
            self::$protocol = 'HTTP/1.1';
        }

        // $host
        if (isset($_SERVER['HTTP_HOST']) && strlen($_SERVER['HTTP_HOST']) > 0) {
            self::$host = $_SERVER['HTTP_HOST'];
        } else {
            if (isset($_SERVER['SERVER_NAME'])) {
                self::$host = $_SERVER['SERVER_NAME'];
            } elseif (isset($_SERVER['SERVER_ADDR'])) {
                self::$host = $_SERVER['SERVER_ADDR'];
            } else {
                self::$host = $_SERVER['LOCAL_ADDR'];
            }

            if (isset($_SERVER['SERVER_PORT'])) {
                if (self::$https) {
                    if ($_SERVER['SERVER_PORT'] !== '443') {
                        self::$host .= $_SERVER['SERVER_PORT'];
                    }
                } else {
                    if ($_SERVER['SERVER_PORT'] !== '80') {
                        self::$host .= $_SERVER['SERVER_PORT'];
                    }
                }
            }
        }

        // $method, $isAjax, $wrapperFunction, etc.
        if (isset($_SERVER['X-HTTP-METHOD-OVERRIDE'])) {
            self::$method = self::$methodext = strtolower($_SERVER['X-HTTP-METHOD-OVERRIDE']);
        } elseif (isset($_POST) && isset($_POST['_method'])) {
            self::$method = self::$methodext = strtolower($_POST['_method']);
        } else {
            self::$method = self::$methodext = strtolower($_SERVER['REQUEST_METHOD']);
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
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

        // $requestUri
        if (isset($_SERVER['X_ORIGINAL_URL'])) {
            self::$requestUri = $_SERVER['X_ORIGINAL_URL'];
        } elseif (isset($_SERVER['X_REWRITE_URL'])) {
            self::$requestUri = $_SERVER['X_REWRITE_URL'];
        } elseif (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            self::$requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['IIS_WasUrlRewritten']) && (string)$_SERVER['IIS_WasUrlRewritten'] === '1' && isset($_SERVER['UNENCODED_URL'])) {
            self::$requestUri = $_SERVER['UNENCODED_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            if (strncmp($_SERVER['REQUEST_URI'], self::$host, $tHostLength = strlen(self::$host)) === 0) {
                self::$requestUri = substr($_SERVER['REQUEST_URI'], $tHostLength);
            } else {
                self::$requestUri = $_SERVER['REQUEST_URI'];
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            self::$requestUri = $_SERVER['ORIG_PATH_INFO'];

            if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0) {
                self::$requestUri .= '?' . $_SERVER['QUERY_STRING'];
            }
        } else {
            self::$requestUri = null;
        }

        Framework::$variables['host'] = self::$host;
        Framework::$variables['scheme'] = self::$https ? 'https' : 'http';
        Framework::$variables['method'] = self::$method;
    }

    /**
     * @ignore
     */
    public static function setRoutes()
    {
        // $siteroot
        if (self::$siteroot === null) {
            self::$siteroot = Config::get(
                'options/siteroot',
                strtr(pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME), '\\', '/')
            );
        }

        self::$siteroot = trim(self::$siteroot, '/');
        if (strlen(self::$siteroot) > 0) {
            self::$siteroot = '/' . self::$siteroot;
        }

        // $pathinfo
        if (($tPos = strpos(self::$requestUri, '?')) !== false) {
            $tBaseUriPath = substr(self::$requestUri, 0, $tPos);
        } else {
            $tBaseUriPath = self::$requestUri;
        }

        self::$pathInfo = ltrim(substr($tBaseUriPath, strlen(self::$siteroot)), '/');

        // $route
        self::$route = Framework::$application->resolve(self::$pathInfo, self::$methodext);

        Framework::$variables['root'] = self::$siteroot;
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
        if ($uFilter === self::FILTER_VALIDATE_BOOLEAN) {
            if ($uValue === true || $uValue === 'true' || $uValue === 1 || $uValue === '1' ||
                $uValue === false || $uValue === 'false' || $uValue === 0 || $uValue === '0') {
                return true;
            }

            return false;
        }

        if ($uFilter === self::FILTER_SANITIZE_BOOLEAN) {
            if ($uValue === true || $uValue === 'true' || $uValue === 1 || $uValue === '1') {
                return true;
            }

            return false;
        }

        if ($uFilter === self::FILTER_SANITIZE_XSS) {
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
        if ($uLanguage === null) {
            return self::$languages;
        }

        return in_array(strtolower($uLanguage), self::$languages);
    }

    /**
     * @ignore
     */
    public static function checkContentType($uContentType = null)
    {
        if ($uContentType === null) {
            return self::$contentTypes;
        }

        return in_array(strtolower($uContentType), self::$contentTypes);
    }

    /**
     * @ignore
     */
    public static function matchesHostname($uAddress)
    {
        $tParsed = parse_url($uAddress);

        if (!isset($tParsed['port'])) {
            $tParsed['port'] = ($tParsed['scheme'] === 'https') ? 443 : 80;
        }

        if ($_SERVER['SERVER_NAME'] === $tParsed['host'] && $_SERVER['SERVER_PORT'] === $tParsed['port']) {
            return true;
        }

        return false;
    }

    /**
     * @ignore
     */
    public static function get($uKey, $uDefault = null, $uFilter = self::FILTER_SANITIZE_XSS)
    {
        if (!isset($_GET[$uKey])) {
            return $uDefault;
        }

        if (get_magic_quotes_gpc() && is_string($_GET[$uKey])) {
            $tValue = stripslashes($_GET[$uKey]);
        } else {
            $tValue = $_GET[$uKey];
        }

        if ($uFilter === null) {
            return $tValue;
        }

        if (func_num_args() > 2) {
            $tNewArgs = array_slice(func_get_args(), 2);
            array_unshift($tNewArgs, $tValue);
        } else {
            $tNewArgs = array($tValue, $uFilter);
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

        if (get_magic_quotes_gpc() && is_string($_POST[$uKey])) {
            $tValue = stripslashes($_POST[$uKey]);
        } else {
            $tValue = $_POST[$uKey];
        }

        if ($uFilter === null) {
            return $tValue;
        }

        if (func_num_args() > 2) {
            $tNewArgs = array_slice(func_get_args(), 2);
            array_unshift($tNewArgs, $tValue);
        } else {
            $tNewArgs = array($tValue, $uFilter);
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

        if (get_magic_quotes_gpc() && is_string($_COOKIE[$uKey])) {
            $tValue = stripslashes($_COOKIE[$uKey]);
        } else {
            $tValue = $_COOKIE[$uKey];
        }

        if ($uFilter === null) {
            return $tValue;
        }

        if (func_num_args() > 2) {
            $tNewArgs = array_slice(func_get_args(), 2);
            array_unshift($tNewArgs, $tValue);
        } else {
            $tNewArgs = array($tValue, $uFilter);
        }

        return call_user_func_array('Scabbia\\Request::filter', $tNewArgs);
    }


    /**
     * @ignore
     */
    public static function getArray($uKeys, $uFilter = self::FILTER_SANITIZE_XSS)
    {
        $tValues = array();
        if ($uFilter !== null) {
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

            if (get_magic_quotes_gpc() && is_string($_GET[$tKey])) {
                $tValue = stripslashes($_GET[$tKey]);
            } else {
                $tValue = $_GET[$tKey];
            }

            if (!isset($tArgs)) {
                $tValues[$tKey] = $tValue;
                continue;
            }

            $tNewArgs = $tArgs;
            array_unshift($tNewArgs, $tValue);

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
        if ($uFilter !== null) {
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

            if (get_magic_quotes_gpc() && is_string($_POST[$tKey])) {
                $tValue = stripslashes($_POST[$tKey]);
            } else {
                $tValue = $_POST[$tKey];
            }

            if (!isset($tArgs)) {
                $tValues[$tKey] = $tValue;
                continue;
            }

            $tNewArgs = $tArgs;
            array_unshift($tNewArgs, $tValue);

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
        if ($uFilter !== null) {
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

            if (get_magic_quotes_gpc() && is_string($_COOKIE[$tKey])) {
                $tValue = stripslashes($_COOKIE[$tKey]);
            } else {
                $tValue = $_COOKIE[$tKey];
            }

            if (!isset($tArgs)) {
                $tValues[$tKey] = $tValue;
                continue;
            }

            $tNewArgs = $tArgs;
            array_unshift($tNewArgs, $tValue);

            $tValues[$tKey] = call_user_func_array('Scabbia\\Request::filter', $tNewArgs);
        }

        return $tValues;
    }
}
