<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\LarouxJs;

use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Http\Request;
use Scabbia\Extensions\Http\Response;
use Scabbia\Extensions\Mvc\Mvc;

/**
 * LarouxJs Extension
 *
 * @package Scabbia
 * @subpackage LarouxJs
 * @version 1.1.0
 *
 * @todo translations
 */
class LarouxJs
{
    /**
     * @ignore
     */
    public static function output($uParms)
    {
        if (Request::$isAjax && Request::$wrapperFunction == 'laroux.js') {
            $tLastContentType = Response::sentHeaderValue('Content-Type');
            $tContent = '{ "isSuccess": ' . (($uParms['exitStatus'][0] > 0) ? 'false' : 'true') .
                ', "errorMessage": ' .
                (is_null($uParms['exitStatus']) ? 'null' : String::dquote($uParms['exitStatus'][1], true));

            if ($tLastContentType == false) {
                header('Content-Type: application/json', true);

                $tContent .= ', "object": ' . json_encode($uParms['content']);
            } else {
                $tContent .= ', "object": ' . $uParms['content'];
            }

            $tContent .= ' }';

            header('X-Response-Wrapper-Function: laroux.js', true);
            $uParms['content'] = $tContent;
        }
    }

    /**
     * @ignore
     */
    public static function exportJs()
    {
        $tArray = Mvc::export(true);

        $tReturn = <<<EOD
\$l.ready(function() {
    \$l.extend({
EOD;
        foreach ($tArray as $tClassName => $tClass) {
            if (($tPos = strrpos($tClassName, '\\')) !== false) {
                $tClassName = substr($tClassName, $tPos + 1);
            }

            $tLines = array();

            if (isset($tFirst)) {
                $tReturn .= ',';
            } else {
                $tFirst = false;
            }

            $tReturn .= PHP_EOL . "\t\t\t" . $tClassName . ': {' . PHP_EOL;

            foreach ($tClass as $tMethod) {
                $tMethods = explode('_', $tMethod, 2);
                if (count($tMethods) < 2 || strpos($tMethods[0], 'ajax') === false) {
                    continue;
                }

                $tLines[] = "\t\t\t\t" .
                    $tMethods[1] .
                    ': function(values, fnc, method) { $l.ajax.request(\'' .
                    Http::url($tClassName . '/' . strtr($tMethods[1], '_', '/')) .
                    '\', values, fnc, method); }';
            }
            $tReturn .= implode(',' . PHP_EOL, $tLines) . PHP_EOL . "\t\t\t" . '}';
        }
        $tReturn .= <<<EOD

    });
});
EOD;

        return $tReturn;
    }
}
