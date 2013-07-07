<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Http;

use Scabbia\Extensions\Mime\Mime;
use Scabbia\Extensions\I18n\I18n;
use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Request;
use Psr\Log\LogLevel;

/**
 * Http Extension
 *
 * @package Scabbia
 * @subpackage Http
 * @version 1.1.0
 */
class Http
{
    /**
     * @ignore
     */
    const BUILDURL_STRIP_NONE = 0;
    /**
     * @ignore
     */
    const BUILDURL_STRIP_USER = 1;          // Strip any user authentication information
    /**
     * @ignore
     */
    const BUILDURL_STRIP_PASS = 2;          // Strip any password authentication information
    /**
     * @ignore
     */
    const BUILDURL_STRIP_AUTH = 3;          // Strip any authentication information
    /**
     * @ignore
     */
    const BUILDURL_STRIP_PORT = 4;          // Strip explicit port numbers
    /**
     * @ignore
     */
    const BUILDURL_STRIP_PATH = 8;          // Strip complete path
    /**
     * @ignore
     */
    const BUILDURL_STRIP_QUERY = 16;        // Strip query string
    /**
     * @ignore
     */
    const BUILDURL_STRIP_FRAGMENT = 32;     // Strip any fragments (#identifier)
    /**
     * @ignore
     */
    const BUILDURL_STRIP_ALL = 63;          // Strip anything but scheme and host


    /**
     * @ignore
     */
    public static $errorPages = array();


    /**
     * @ignore
     */
    public static function routing()
    {
        $tResolution = Framework::$application->resolve(Request::$queryString, Request::$method, Request::$methodext);

        if ($tResolution !== null && call_user_func($tResolution[1], $tResolution[2]) !== false) {
            // to interrupt event-chain execution
            return true;
        }

        return null;
    }

    /**
     * @ignore
     */
    public static function url($uPath, $uFull = false)
    {
        $tResolved = Framework::$application->resolve($uPath);
        if ($uFull) {
            return
                (Request::$https ? 'https://' : 'http://') .
                Request::$host .
                Request::$siteroot .
                '/' .
                $tResolved[0];
        }

        return Request::$siteroot . '/' . $tResolved[0];
    }

    /**
     * @ignore
     */
    public static function checkUserAgent()
    {
        $tReturn = array();

        foreach (Config::get('http/userAgents/platformList', array()) as $tPlatformList) {
            if (preg_match('/' . $tPlatformList['match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
                $tReturn['platform'] = $tPlatformList['name'];
                break;
            }
        }

        foreach (Config::get('http/userAgents/crawlerList', array()) as $tCrawlerList) {
            if (preg_match('/' . $tCrawlerList['match'] . '/i', $_SERVER['HTTP_USER_AGENT'])) {
                $tReturn['crawler'] = $tCrawlerList['name'];
                $tReturn['crawlerType'] = $tCrawlerList['type'];

                if ($tCrawlerList['type'] === 'bot') {
                    $tReturn['isRobot'] = true;
                } elseif ($tCrawlerList['type'] === 'mobile') {
                    $tReturn['isMobile'] = true;
                } else { // if ($tCrawlerList['type'] === 'browser') {
                    $tReturn['isBrowser'] = true;
                }

                break;
            }
        }
    }

    /**
     * @ignore
     */
    public static function redirect($uPath, $uTerminate = true)
    {
        self::sendRedirect(self::url($uPath), $uTerminate);
    }

    /**
     * @ignore
     */
    public static function notfound()
    {
        header(Request::$protocol . ' 404 Not Found', true, 404);
        self::error('notfound', I18n::_('404 Not Found'), I18n::_('The resource you have been looking for is not found on the server'));
    }

    /**
     * @ignore
     */
    public static function error($uErrorType, $uTitle = null, $uMessage = null)
    {
        if (!isset(self::$errorPages[$uErrorType])) {
            self::$errorPages[$uErrorType] = Config::get('http/errorPages/' . $uErrorType, '{core}views/shared/error.php');
        }

        //! todo internalization.
        // maybe just include?
        Views::viewFile(
            self::$errorPages[$uErrorType],
            array(
                'title' => $uTitle,
                'message' => $uMessage
            )
        );

        Framework::end(1);
    }

    /**
     * @ignore
     */
    public static function buildUrl(array $uParts, $uFlags = self::BUILDURL_STRIP_NONE)
    {
        $tKeys = array('user', 'pass', 'port', 'path', 'query', 'fragment');
        foreach ($tKeys as $tKey) {
            if ($uFlags & constant('self::BUILDURL_STRIP_' . strtoupper($tKey))) {
                unset($uParts[$tKey]);
            }
        }

        return
            ((isset($uParts['scheme'])) ? $uParts['scheme'] . '://' : "")
            .((isset($uParts['user'])) ? $uParts['user'] . ((isset($uParts['pass'])) ? ':' . $uParts['pass'] : "") .'@' : "")
            .((isset($uParts['host'])) ? $uParts['host'] : "")
            .((isset($uParts['port'])) ? ':' . $uParts['port'] : "")
            .((isset($uParts['path'])) ? $uParts['path'] : "")
            .((isset($uParts['query'])) ? '?' . $uParts['query'] : "")
            .((isset($uParts['fragment'])) ? '#' . $uParts['fragment'] : "")
            ;
    }

    /**
     * @ignore
     */
    private static function buildQueryString_arr(&$uParameters, $uKey, array $uValue)
    {
        foreach ($uValue as $tValue) {
            if (is_array($tValue)) {
                self::buildQueryString_arr($uParameters, $uKey . '[]', $tValue);
                continue;
            }

            $uParameters[] = $uKey . '[]=' . rawurlencode($tValue);
        }
    }

    /**
     * @ignore
     */
    public static function buildQueryString($uParameters)
    {
        $tParameters = array();

        ksort($uParameters, SORT_STRING);
        foreach ($uParameters as $tKey => $tValue) {
            $tEncodedKey = rawurlencode($tKey);

            if (is_array($tValue)) {
                self::buildQueryString_arr($tParameters, $tEncodedKey, $tValue);
                continue;
            }

            $tParameters[] = $tEncodedKey . '=' . rawurlencode($tValue);
        }

        return implode('&', $tParameters);
    }


    /**
     * @ignore
     *
     * RFC 3986
     */
    public static function encode($uString)
    {
        return rawurlencode($uString);
    }

    /**
     * @ignore
     */
    public static function decode($uString)
    {
        return urldecode($uString);
    }

    /**
     * @ignore
     */
    public static function encodeArray($uArray)
    {
        $tReturn = array();

        foreach ($uArray as $tKey => $tValue) {
            $tReturn[] = rawurlencode($tKey) . '=' . rawurlencode($tValue);
        }

        return implode('&', $tReturn);
    }

    /**
     * @ignore
     */
    public static function copyStream($tFilename)
    {
        $tInput = fopen('php://input', 'rb');
        $tOutput = fopen($tFilename, 'wb');
        stream_copy_to_stream($tInput, $tOutput);
        fclose($tOutput);
        fclose($tInput);
    }

    /**
     * @ignore
     */
    public static function reportError(array $uParms)
    {
        if (!$uParms['halt']) {
            return;
        }

        header(Request::$protocol . ' 500 Internal Server Error', true, 500);
        header('Content-Type: text/html, charset=UTF-8', true);

        /*
        $tLastContentType = self::sentHeaderValue('Content-Type');
        if ($tLastContentType === false) {
            header('Content-Type: text/html, charset=UTF-8', true);
        }
        */

        for ($tCount = ob_get_level(); --$tCount > 1; ob_end_flush()) {
            ;
        }

        // @todo: check requested format instead. like home/index.json
        if (Request::$isAjax) {
            $tArray = array(
                'category' => $uParms['category'],
                'location' => $uParms['location'],
                'message' => $uParms['message']
            );

            if (Framework::$development) {
                if (count($uParms['eventDepth']) > 0) {
                    $tArray['eventDepth'] = $uParms['eventDepth'];
                }

                if (count($uParms['stackTrace']) > 0) {
                    $tArray['eventDepth'] = $uParms['stackTrace'];
                }
            }

            $tString = json_encode($tArray);
        } else {
            $tString  = '<pre style="font-family: \'Consolas\', monospace;">'; // for content-type: text/xml
            $tString .= '<div style="font-size: 11pt; color: #000060; border-bottom: 1px solid #C0C0C0; background: #F0F0F0; padding: 8px 12px 8px 12px;"><span style="font-weight: bold;">' . $uParms['category'] . '</span>: ' . $uParms['location'] . '</div>' . PHP_EOL;
            $tString .= '<div style="font-size: 10pt; color: #404040; padding: 0px 12px 0px 12px; line-height: 20px;">' . $uParms['message'] . '</div>' . PHP_EOL . PHP_EOL;

            if (Framework::$development) {
                if (count($uParms['eventDepth']) > 0) {
                    $tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; line-height: 20px;"><b>eventDepth:</b>' . PHP_EOL . implode(PHP_EOL, $uParms['eventDepth']) . '</div>' . PHP_EOL . PHP_EOL;
                }

                if (count($uParms['stackTrace']) > 0) {
                    $tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; line-height: 20px;"><b>stackTrace:</b>' . PHP_EOL . implode(PHP_EOL, $uParms['stackTrace']) . '</div>' . PHP_EOL . PHP_EOL;
                }
            }

            $tString .= '</pre>';
            $tString .= '<div style="font-size: 7pt; color: #808080; padding: 0px 12px 0px 12px;">Generated by <a href="http://larukedi.github.com/Scabbia-Framework/">Scabbia Framework</a>.</div>' . PHP_EOL;
        }

        echo $tString;
    }

    /**
     * @ignore
     */
    public static function sendStatus($uStatusCode)
    {
        $tStatus = Request::$protocol . ' ';

        if ($uStatusCode === 100) {
            $tStatus .= '100 Continue';
        } elseif ($uStatusCode === 101) {
            $tStatus .= '101 Switching Protocols';
        } elseif ($uStatusCode === 200) {
            $tStatus .= '200 OK';
        } elseif ($uStatusCode === 201) {
            $tStatus .= '201 Created';
        } elseif ($uStatusCode === 202) {
            $tStatus .= '202 Accepted';
        } elseif ($uStatusCode === 203) {
            $tStatus .= '203 Non-Authoritative Information';
        } elseif ($uStatusCode === 204) {
            $tStatus .= '204 No Content';
        } elseif ($uStatusCode === 205) {
            $tStatus .= '205 Reset Content';
        } elseif ($uStatusCode === 206) {
            $tStatus .= '206 Partial Content';
        } elseif ($uStatusCode === 300) {
            $tStatus .= '300 Multiple Choices';
        } elseif ($uStatusCode === 301) {
            $tStatus .= '301 Moved Permanently';
        } elseif ($uStatusCode === 302) {
            $tStatus .= '302 Found';
        } elseif ($uStatusCode === 303) {
            $tStatus .= '303 See Other';
        } elseif ($uStatusCode === 304) {
            $tStatus .= '304 Not Modified';
        } elseif ($uStatusCode === 305) {
            $tStatus .= '305 Use Proxy';
        } elseif ($uStatusCode === 307) {
            $tStatus .= '307 Temporary Redirect';
        } elseif ($uStatusCode === 400) {
            $tStatus .= '400 Bad Request';
        } elseif ($uStatusCode === 401) {
            $tStatus .= '401 Unauthorized';
        } elseif ($uStatusCode === 402) {
            $tStatus .= '402 Payment Required';
        } elseif ($uStatusCode === 403) {
            $tStatus .= '403 Forbidden';
        } elseif ($uStatusCode === 404) {
            $tStatus .= '404 Not Found';
        } elseif ($uStatusCode === 405) {
            $tStatus .= '405 Method Not Allowed';
        } elseif ($uStatusCode === 406) {
            $tStatus .= '406 Not Acceptable';
        } elseif ($uStatusCode === 407) {
            $tStatus .= '407 Proxy Authentication Required';
        } elseif ($uStatusCode === 408) {
            $tStatus .= '408 Request Timeout';
        } elseif ($uStatusCode === 409) {
            $tStatus .= '409 Conflict';
        } elseif ($uStatusCode === 410) {
            $tStatus .= '410 Gone';
        } elseif ($uStatusCode === 411) {
            $tStatus .= '411 Length Required';
        } elseif ($uStatusCode === 412) {
            $tStatus .= '412 Precondition Failed';
        } elseif ($uStatusCode === 413) {
            $tStatus .= '413 Request Entity Too Large';
        } elseif ($uStatusCode === 414) {
            $tStatus .= '414 Request-URI Too Long';
        } elseif ($uStatusCode === 415) {
            $tStatus .= '415 Unsupported Media Type';
        } elseif ($uStatusCode === 416) {
            $tStatus .= '416 Requested Range Not Satisfiable';
        } elseif ($uStatusCode === 417) {
            $tStatus .= '417 Expectation Failed';
        } elseif ($uStatusCode === 500) {
            $tStatus .= '500 Internal Server Error';
        } elseif ($uStatusCode === 501) {
            $tStatus .= '501 Not Implemented';
        } elseif ($uStatusCode === 502) {
            $tStatus .= '502 Bad Gateway';
        } elseif ($uStatusCode === 503) {
            $tStatus .= '503 Service Unavailable';
        } elseif ($uStatusCode === 504) {
            $tStatus .= '504 Gateway Timeout';
        } elseif ($uStatusCode === 505) {
            $tStatus .= '505 HTTP Version Not Supported';
        } else {
            return;
        }

        header($tStatus, true, $uStatusCode);
    }

    /**
     * @ignore
     */
    public static function sendHeader($uHeader, $uValue = null, $uReplace = false)
    {
        if (isset($uValue)) {
            header($uHeader . ': ' . $uValue, $uReplace);
        } else {
            header($uHeader, $uReplace);
        }
    }

    /**
     * @ignore
     */
    public static function sentHeaderValue($uKey)
    {
        foreach (headers_list() as $tHeaderRow) {
            $tHeader = explode(': ', $tHeaderRow, 2);

            if (count($tHeader) < 2) {
                continue;
            }

            if (strcasecmp($tHeader[0], $uKey) === 0) {
                return $tHeader[1];
            }
        }

        return false;
    }

    /**
     * @ignore
     */
    public static function sendFile($uFilePath, $uAttachment = false, $uFindMimeType = true)
    {
        $tExtension = pathinfo($uFilePath, PATHINFO_EXTENSION);

        if ($uFindMimeType) {
            $tType = Mime::getType($tExtension);
        } else {
            $tType = 'application/octet-stream';
        }

        self::sendHeaderCache(-1);
        header('Accept-Ranges: bytes', true);
        header('Content-Type: ' . $tType, true);
        if ($uAttachment) {
            header('Content-Disposition: attachment; filename=' . pathinfo($uFilePath, PATHINFO_BASENAME) . ';', true);
        }
        header('Content-Transfer-Encoding: binary', true);
        //! filesize problem
        // header('Content-Length: ' . filesize($uFilePath), true);
        header('ETag: "' . md5_file($uFilePath) . '"', true);
        readfile($uFilePath, false);

        Framework::end(0);
    }

    /**
     * @ignore
     */
    public static function sendHeaderLastModified($uTime, $uNotModified = false)
    {
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $uTime) . ' GMT', true);

        if ($uNotModified) {
            self::sendStatus(304);
        }
    }

    /**
     * @ignore
     */
    public static function sendRedirect($uLocation, $uTerminate = true)
    {
        header('Location: ' . $uLocation, true);

        if ($uTerminate) {
            Framework::end(0);
        }
    }

    /**
     * @ignore
     */
    public static function sendRedirectPermanent($uLocation, $uTerminate = true)
    {
        self::sendStatus(301);
        header('Location: ' . $uLocation, true);

        if ($uTerminate) {
            Framework::end(0);
        }
    }

    /**
     * @ignore
     */
    public static function sendHeaderETag($uHash)
    {
        header('ETag: "' . $uHash . '"', true);
    }

    /**
     * @ignore
     */
    public static function sendHeaderCache($uTtl = -1, $uPublic = true, $uMustRevalidate = false)
    {
        if ($uTtl < 0) {
            if (Request::$protocol === 'HTTP/1.0') { // http/1.0 only
                header('Pragma: no-cache', true);
                header('Expires: Thu, 01 Jan 1970 00:00:00 GMT', true);

                return;
            }

            header(
                'Cache-Control: ' . (
                ($uMustRevalidate) ?
                    'no-store, no-cache, must-revalidate' :
                    'no-store, no-cache'
                ),
                true
            );

            return;
        }

        if ($uPublic) {
            $tPublicity = 'public';
        } else {
            $tPublicity = 'private';
        }

        if (Request::$protocol === 'HTTP/1.0') { // http/1.0 only
            header('Pragma: ' . $tPublicity, true);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $uTtl) . ' GMT', true);

            return;
        }

        if ($uMustRevalidate) {
            $tPublicity .= ', must-revalidate';
        }

        header('Cache-Control: max-age=' . $uTtl . ', ' . $tPublicity, true);
    }

    /**
     * @ignore
     */
    public static function sendCookie($uCookie, $uValue, $uExpire = -1)
    {
        if ($uExpire === -1) {
            $uExpire = time() + (60 * 60 * 24 * 365); // a year

        }

        setrawcookie($uCookie, self::encode($uValue), $uExpire, Request::$siteroot . '/');
    }

    /**
     * @ignore
     */
    public static function removeCookie($uCookie)
    {
        setrawcookie($uCookie, "", time() - 3600, Request::$siteroot . '/');
    }
}
