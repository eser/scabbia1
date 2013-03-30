<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Io;

/**
 * Views Extension: ViewEngineRazor Class
 *
 * @package Scabbia
 * @subpackage Views
 * @version 1.1.0
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
    public static $compiledTtl;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$compiledTtl = (int)Config::get('razor/templates/compiledTtl', 120);
        Views::registerViewEngine('cshtml', 'Scabbia\\Extensions\\Views\\ViewEngineRazor');
    }

    /**
     * @ignore
     *
     * @throws \Exception
     */
    public static function renderview($uObject)
    {
        $tInputFile = $uObject['templatePath'] . $uObject['templateFile'];

        // cengiz: Render if file not exist
        // or debug mode on
        $tOutputFile = Io::translatePath('{writable}cache/cshtml/' . $uObject['compiledFile']);

        if (Framework::$development >= 1 || !Io::isReadable($tOutputFile, self::$compiledTtl)) {
            if (is_null(self::$engine)) {
                require 'razor/RazorViewRenderer.php';
                require 'razor/RazorViewRendererException.php';

                self::$engine = new \RazorViewRenderer();
            }

            if (Framework::$readonly) {
                throw new \Exception('Framework runs in read only mode.');
            }

            self::$engine->generateViewFile($tInputFile, $tOutputFile);
        }

        // variable extraction
        $model = $uObject['model'];
        if (is_array($model)) {
            extract($model, EXTR_SKIP | EXTR_REFS);
        }

        if (isset($uObject['extra'])) {
            extract($uObject['extra'], EXTR_SKIP | EXTR_REFS);
        }

        require $tOutputFile;
    }
}
