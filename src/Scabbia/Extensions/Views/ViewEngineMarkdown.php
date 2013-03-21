<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Cache\Cache;
use Scabbia\Extensions\IoEx\IoEx;
use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;
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
    public static $compiledAge;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$compiledAge = intval(Config::get('razor/templates/compiledAge', '120'));
        Views::registerViewEngine('md', 'Scabbia\\Extensions\\Views\\ViewEngineMarkdown');
    }

    /**
     * @ignore
     */
    public static function renderview($uObject)
    {
        $tInputFile = $uObject['templatePath'] . $uObject['templateFile'];

        $tOutputFile = Cache::filePath('md/', $uObject['compiledFile'], self::$compiledAge);
        if (Framework::$development >= 1 || !$tOutputFile[0]) {
            if (is_null(self::$engine)) {
                self::$engine = new MarkdownExtraParser();
            }

            $tInput = IoEx::read($tInputFile);
            $tOutput = self::$engine->transformMarkdown($tInput);

            if (!is_null($tOutputFile[1])) {
                IoEx::write($tOutputFile[1], $tOutput);
            }
            echo $tOutput;
        } else {
            require $tOutputFile[1];
        }
    }
}
