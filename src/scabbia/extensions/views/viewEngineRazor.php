<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Views\Views;
use Scabbia\Extensions\Views\Razor\RazorViewRenderer;
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
     *
     * @throws \Exception
     */
    public static function renderview($uObject)
    {
        $tInputFile = $uObject['templatePath'] . $uObject['templateFile'];
        $tOutputFile = Io::translatePath('{writable}cache/cshtml/' . $uObject['compiledFile']);

        if (Framework::$disableCaches || !Io::isReadableAndNewer($tOutputFile, filemtime($tInputFile))) {
            if (self::$engine === null) {
                self::$engine = new RazorViewRenderer();
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

        extract(Framework::$variables, EXTR_SKIP | EXTR_REFS);

        require $tOutputFile;
    }
}
