<?php

namespace Scabbia\Extensions\Http;

use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Http\Response;
use Scabbia\Extensions\String\String;
use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Framework;

/**
 * Http Extension
 *
 * @package Scabbia
 * @subpackage http
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends string
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
 */
class Http
{
    /**
     * @ignore
     */
    public static $notfoundPage = null;


    /**
     * @ignore
     */
    public static function url($uPath)
    {
        $tParms = array(
            'siteroot' => rtrim(Framework::$siteroot, '/'),
            'device' => Request::$crawlerType,
            'path' => $uPath
        );

        Events::invoke('httpUrl', $tParms);

        return String::format(Config::get('/http/link', '{@siteroot}/{@path}'), $tParms);
    }

    /**
     * @ignore
     */
    public static function notfound()
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);

        // $notfoundPage
        if (is_null(self::$notfoundPage)) {
            self::$notfoundPage = Config::get('/http/errorPages/notfound', '{app}views/shared/error.php');
        }

        //! todo internalization.
        // maybe just include?
        Views::viewFile(self::$notfoundPage, array(
                                           'title' => 'Error',
                                           'message' => '404 Not Found'
                                      ));

        Framework::end(1);
    }

    /**
     * @ignore
     */
    public static function output($uParms)
    {
        if (Request::$isAjax) {
            $tLastContentType = Response::sentHeaderValue('Content-Type');
            $tContent = '{ "isSuccess": ' . (($uParms['exitStatus'][0] > 0) ? 'false' : 'true')
                    . ', "errorMessage": ' . (is_null($uParms['exitStatus']) ? 'null' : String::dquote($uParms['exitStatus'][1], true));

            if ($tLastContentType == false) {
                Response::sendHeader('Content-Type', 'application/json', true);

                $tContent .= ', "object": ' . json_encode($uParms['content']);
            } else {
                $tContent .= ', "object": ' . $uParms['content'];
            }

            $tContent .= ' }';

            $uParms['content'] = $tContent;
        }
    }

    /**
     * @ignore
     */
    public static function xss($uString)
    {
        if (is_string($uString)) {
            $tString = str_replace(array('<', '>', '"', '\'', '$', '(', ')', '%28', '%29'), array('&#60;', '&#62;', '&#34;', '&#39;', '&#36;', '&#40;', '&#41;', '&#40;', '&#41;'), $uString); // '&' => '&#38;'
            return $tString;
        }

        return $uString;
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
}
