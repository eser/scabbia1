<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Views;

use Scabbia\Framework;

/**
 * Views Extension: ViewEnginePhp Class
 *
 * @package Scabbia
 * @subpackage Views
 * @version 1.1.0
 */
class ViewEnginePhp
{
    /**
     * @ignore
     */
    public static function renderview($uObject)
    {
        // variable extraction
        $model = $uObject['model'];
        if (is_array($model)) {
            extract($model, EXTR_SKIP | EXTR_REFS);
        }

        extract(Framework::$variables, EXTR_SKIP | EXTR_REFS);

        require $uObject['templatePath'] . $uObject['templateFile'];
    }
}
