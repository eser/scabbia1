<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\LarouxJs;

use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Mvc\Controllers;
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
        translations:
EOD;
        $tReturn .= json_encode(self::$translations);

        $tReturn .= <<<EOD

});
EOD;

        return $tReturn;
    }
}
