<?php

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Cache\Cache;
use Scabbia\Extensions\Io\Io;
use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;

require 'markdownExtra/markdownExtra.php';
require 'markdownExtra/markdownParser.php';
require 'markdownExtra/markdownExtraParser.php';

/**
 * ViewEngine: MarkDown Extension
 *
 * @package Scabbia
 * @subpackage viewEngineMarkdown
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends mvc, io, cache
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
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
        self::$compiledAge = intval(Config::get('/razor/templates/compiledAge', '120'));
        Views::registerViewEngine('md', 'viewEngineMarkdown');
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
                self::$engine = new \MarkdownExtra_Parser();
            }

            $tInput = Io::read($tInputFile);
            $tOutput = self::$engine->transform($tInput);

            if (!is_null($tOutputFile[1])) {
                Io::write($tOutputFile[1], $tOutput);
            }
            echo $tOutput;
        } else {
            require $tOutputFile[1];
        }
    }
}
