<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Views;

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

        if (isset($uObject['extra'])) {
            extract($uObject['extra'], EXTR_SKIP | EXTR_REFS);
        }

        require $uObject['templatePath'] . $uObject['templateFile'];
    }
}
