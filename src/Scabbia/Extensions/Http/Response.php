<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Http;

use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Mime\Mime;
use Scabbia\Framework;

/**
 * Http Extension: Response Class
 *
 * @package Scabbia
 * @subpackage Http
 * @version 1.1.0
 */
class Response
{
    /**
     * @ignore
     */
    public static function sendStatus($uStatusCode)
    {
        $tStatus = $_SERVER['SERVER_PROTOCOL'] . ' ';

        switch ($uStatusCode) {
            case 100:
                $tStatus .= '100 Continue';
                break;
            case 101:
                $tStatus .= '101 Switching Protocols';
                break;
            case 200:
                $tStatus .= '200 OK';
                break;
            case 201:
                $tStatus .= '201 Created';
                break;
            case 202:
                $tStatus .= '202 Accepted';
                break;
            case 203:
                $tStatus .= '203 Non-Authoritative Information';
                break;
            case 204:
                $tStatus .= '204 No Content';
                break;
            case 205:
                $tStatus .= '205 Reset Content';
                break;
            case 206:
                $tStatus .= '206 Partial Content';
                break;
            case 300:
                $tStatus .= '300 Multiple Choices';
                break;
            case 301:
                $tStatus .= '301 Moved Permanently';
                break;
            case 302:
                $tStatus .= '302 Found';
                break;
            case 303:
                $tStatus .= '303 See Other';
                break;
            case 304:
                $tStatus .= '304 Not Modified';
                break;
            case 305:
                $tStatus .= '305 Use Proxy';
                break;
            case 307:
                $tStatus .= '307 Temporary Redirect';
                break;
            case 400:
                $tStatus .= '400 Bad Request';
                break;
            case 401:
                $tStatus .= '401 Unauthorized';
                break;
            case 402:
                $tStatus .= '402 Payment Required';
                break;
            case 403:
                $tStatus .= '403 Forbidden';
                break;
            case 404:
                $tStatus .= '404 Not Found';
                break;
            case 405:
                $tStatus .= '405 Method Not Allowed';
                break;
            case 406:
                $tStatus .= '406 Not Acceptable';
                break;
            case 407:
                $tStatus .= '407 Proxy Authentication Required';
                break;
            case 408:
                $tStatus .= '408 Request Timeout';
                break;
            case 409:
                $tStatus .= '409 Conflict';
                break;
            case 410:
                $tStatus .= '410 Gone';
                break;
            case 411:
                $tStatus .= '411 Length Required';
                break;
            case 412:
                $tStatus .= '412 Precondition Failed';
                break;
            case 413:
                $tStatus .= '413 Request Entity Too Large';
                break;
            case 414:
                $tStatus .= '414 Request-URI Too Long';
                break;
            case 415:
                $tStatus .= '415 Unsupported Media Type';
                break;
            case 416:
                $tStatus .= '416 Requested Range Not Satisfiable';
                break;
            case 417:
                $tStatus .= '417 Expectation Failed';
                break;
            case 500:
                $tStatus .= '500 Internal Server Error';
                break;
            case 501:
                $tStatus .= '501 Not Implemented';
                break;
            case 502:
                $tStatus .= '502 Bad Gateway';
                break;
            case 503:
                $tStatus .= '503 Service Unavailable';
                break;
            case 504:
                $tStatus .= '504 Gateway Timeout';
                break;
            case 505:
                $tStatus .= '505 HTTP Version Not Supported';
                break;
            default:
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

            if (strcasecmp($tHeader[0], $uKey) == 0) {
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
        header('Location' . $uLocation, true);

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
            if ($_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1') { // http/1.0 only
                header('Pragma: no-cache', true);
                header('Expires: Thu, 01 Jan 1970 00:00:00 GMT', true);

                return;
            }

            header('Cache-Control: ' . (($uMustRevalidate) ? 'no-store, no-cache, must-revalidate' : 'no-store, no-cache'), true);

            return;
        }

        if ($uPublic) {
            $tPublicity = 'public';
        } else {
            $tPublicity = 'private';
        }

        if ($_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1') { // http/1.0 only
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
    public static function sendCookie($uCookie, $uValue, $uExpire = 0)
    {
        setrawcookie($uCookie, Http::encode($uValue), $uExpire);
    }

    /**
     * @ignore
     */
    public static function removeCookie($uCookie)
    {
        setrawcookie($uCookie, '', time() - 3600);
    }
}
