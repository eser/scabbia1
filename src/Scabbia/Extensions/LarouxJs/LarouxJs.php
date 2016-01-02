<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\LarouxJs;

use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Mvc\Controllers;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Request;

/**
 * LarouxJs Extension
 *
 * @package Scabbia
 * @subpackage LarouxJs
 * @version 1.1.0
 */
class LarouxJs
{
    /**
     * @ignore
     */
    public static $translations = array();


    /**
     * @ignore
     */
    public static function addToDictionary(array $uArray)
    {
        self::$translations += $uArray;
    }

    /**
     * @ignore
     */
    public static function output($uParms)
    {
        if (Request::$wrapperFunction === 'laroux.js') {
            if (Http::sentHeaderValue('Content-Type') === false) {
                header('Content-Type: application/json', true);
            }
            header('X-Response-Wrapper-Function: laroux.js', true);

            $uParms['content'] = '{ "isSuccess": ' . (($uParms['exitStatus'][0] > 0) ? 'false' : 'true') .

                ', "errorMessage": ' .
                ($uParms['exitStatus'] === null ? 'null' : String::dquote($uParms['exitStatus'][1], true)) .

                ', "format": ' .
                String::dquote($uParms['responseFormat'], true) .

                ', "object": ' .
                json_encode($uParms['content']) .

                ' }';
        }
    }

    /**
     * @ignore
     */
    public static function exportJs()
    {
        $tReturn = <<<EOD
\$l.extend({
EOD;

        $tMethods = array();
        foreach (Config::get('larouxjs/methods', array()) as $tMethod) {
            $tSplit = explode('.', $tMethod);
            $tMethodName = array_pop($tSplit);
            // $tNamespace = Framework::$application->name . '\\Controllers\\' . implode('\\', $tSplit);
            $tPath = implode('/', $tSplit);

            if (!isset($tMethods[$tPath])) {
                $tMethods[$tPath] = array();
            }

            $tMethods[$tPath][] = $tMethodName;
        }

        if (count($tMethods) > 0) {
            foreach ($tMethods as $tMethodController => $tMethodActions) {
                $tLines = array();

                if (isset($tFirst)) {
                    $tReturn .= ',';
                } else {
                    $tFirst = false;
                }

                $tReturn .= PHP_EOL . "\t" . str_replace('/', '_', $tMethodController) . ': {' . PHP_EOL;

                foreach ($tMethodActions as $tMethodAction) {
                    $tLines[] = "\t\t" .
                        $tMethodAction .
                        ': function(values, successfnc, errorfnc, method) { if (typeof method == \'undefined\') method = \'post\'; $l.ajax[method](\'' .
                        Http::url($tMethodController . '/' . $tMethodAction) .
                        '\', values, successfnc, errorfnc); }';
                }
                $tReturn .= implode(',' . PHP_EOL, $tLines) . PHP_EOL . "\t" . '}';
            }

            $tReturn .= ',';
        }

        $tReturn .= <<<EOD
        translations:
EOD;
        $tReturn .= json_encode(self::$translations);

        $tReturn .= <<<EOD

});
EOD;

        return $tReturn;
    }
}
