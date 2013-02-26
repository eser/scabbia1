<?php

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Cache\Cache;
use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;

require 'razor/RazorViewRenderer.php';
require 'razor/RazorViewRendererException.php';

/**
 * ViewEngine: Razor Extension
 *
 * @package Scabbia
 * @subpackage viewEngineRazor
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends mvc, cache
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
 */
class ViewEngineRazor
{
    /**
     * @ignore
     */
    public static $engine = null;
    /**
     * @ignore
     */
    public static $compiledAge;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$compiledAge = intval(Config::get('/razor/templates/compiledAge', '120'));
        Views::registerViewEngine('cshtml', 'viewEngineRazor');
    }

    /**
     * @ignore
     */
    public static function renderview($uObject)
    {
        $tInputFile = $uObject['templatePath'] . $uObject['templateFile'];

        // cengiz: Render if file not exist
        // or debug mode on
        $tOutputFile = Cache::filePath('cshtml/', $uObject['compiledFile'], self::$compiledAge);
        if (Framework::$development >= 1 || !$tOutputFile[0]) {
            if (is_null(self::$engine)) {
                self::$engine = new \RazorViewRenderer();
            }

            if (is_null($tOutputFile[1])) {
                throw new \Exception('Framework runs in read only mode.');
            }

            self::$engine->generateViewFile($tInputFile, $tOutputFile[1]);
        }

        // variable extraction
        $model = $uObject['model'];
        if (is_array($model)) {
            extract($model, EXTR_SKIP | EXTR_REFS);
        }

        if (isset($uObject['extra'])) {
            extract($uObject['extra'], EXTR_SKIP | EXTR_REFS);
        }

        require $tOutputFile[1];
    }
}
