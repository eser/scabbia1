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
use dflydev\markdown\MarkdownExtraParser;

/**
 * Views Extension: ViewEngineMarkdown Class
 *
 * @package Scabbia
 * @subpackage Views
 * @version 1.1.0
 */
class ViewEngineMarkdown
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
        // Views::registerViewEngine('md', 'Scabbia\\Extensions\\Views\\ViewEngineMarkdown');
    }

    /**
     * @ignore
     */
    public static function renderview($uObject)
    {
        $tInputFile = $uObject['templatePath'] . $uObject['templateFile'];

        if (is_null(self::$engine)) {
            self::$engine = new MarkdownExtraParser();
        }

        $tInput = Io::read($tInputFile);
        $tOutput = self::$engine->transformMarkdown($tInput);

        echo $tOutput;
    }
}
