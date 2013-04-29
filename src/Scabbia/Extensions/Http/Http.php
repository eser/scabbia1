<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Http;

use Scabbia\Extensions\Profiler\Profiler;
use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Http\Response;
use Scabbia\Extensions\Http\Router;
use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;

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
    public static $errorPages = array();


    /**
     * @ignore
     */
    public static function routing()
    {
        $tResolution = Router::resolve(Request::$queryString, Request::$method, Request::$methodext);

        if (!is_null($tResolution) && call_user_func($tResolution[1], $tResolution[2]) !== false) {
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
        /*
        $tParms = array(
            'siteroot' => Framework::$siteroot,
            'device' => Request::$crawlerType,
            'path' => $uPath
        );

        Events::invoke('httpUrl', $tParms);

        return String::format(Config::get('http/link', '{@siteroot}/{@path}'), $tParms);
        */
        $tResolved = Router::resolve($uPath);
        if ($uFull) {
            return
                (Request::$https ? 'https://' : 'http://') .
                Request::$host .
                Framework::$siteroot .
                '/' .
                $tResolved[0];
        }

        return Framework::$siteroot . '/' . $tResolved[0];
    }

    /**
     * @ignore
     */
    public static function redirect($uPath, $uTerminate = true)
    {
        Response::sendRedirect(self::url($uPath), $uTerminate);
    }

    /**
     * @ignore
     */
    public static function notfound()
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);
        self::error('notfound', _('404 Not Found'), _('The resource you have been looking for is not found on the server'));
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
    public static function encode($uString)
    {
        return urlencode($uString);
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
            $tReturn[] = urlencode($tKey) . '=' . urlencode($tValue);
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
    public static function baseUrl()
    {
        return '//' . $_SERVER['HTTP_HOST'] . Framework::$siteroot;
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
     */
    public static function reportError(array $uParms)
    {
        if ($uParms['ignore']) {
            return;
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        header('Content-Type: text/html, charset=UTF-8', true);

        /*
        $tLastContentType = Response::sentHeaderValue('Content-Type');
        if ($tLastContentType == false) {
            header('Content-Type: text/html, charset=UTF-8', true);
        }
        */

        for ($tCount = ob_get_level(); --$tCount > 1; ob_end_flush()) {
            ;
        }

        $tString  = '<pre style="font-family: \'Consolas\', monospace;">'; // for content-type: text/xml
        $tString .= '<div style="font-size: 11pt; color: #000060; border-bottom: 1px solid #C0C0C0; background: #F0F0F0; padding: 8px 12px 8px 12px;"><span style="font-weight: bold;">' . $uParms['category'] . '</span>: ' . $uParms['location'] . '</div>' . PHP_EOL;
        $tString .= '<div style="font-size: 10pt; color: #404040; padding: 0px 12px 0px 12px; line-height: 20px;">' . $uParms['message'] . '</div>' . PHP_EOL . PHP_EOL;

        if (Framework::$development >= 1) {
            if (count($uParms['eventDepth']) > 0) {
                $tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; line-height: 20px;"><b>eventDepth:</b>' . PHP_EOL . implode(PHP_EOL, $uParms['eventDepth']) . '</div>' . PHP_EOL . PHP_EOL;
            }

            if (count($uParms['stackTrace']) > 0) {
                $tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; line-height: 20px;"><b>stackTrace:</b>' . PHP_EOL . implode(PHP_EOL, $uParms['stackTrace']) . '</div>' . PHP_EOL . PHP_EOL;
            }

            $tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; line-height: 20px;"><b>profiler stack:</b>' . PHP_EOL;
            $tString .= Profiler::exportStack(false);
            $tString .= '</div>' . PHP_EOL;

            $tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; line-height: 20px;"><b>profiler output:</b>' . PHP_EOL;
            $tString .= Profiler::export(false);
            $tString .= '</div>';
        }

        $tString .= '</pre>';
        $tString .= '<div style="font-size: 7pt; color: #808080; padding: 0px 12px 0px 12px;">Generated by <a href="http://larukedi.github.com/Scabbia-Framework/">Scabbia Framework</a>.</div>' . PHP_EOL;

        echo $tString;
    }
}
