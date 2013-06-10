<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
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
    public static function renderview($uObject)
    {
        $tInputFile = $uObject['templatePath'] . $uObject['templateFile'];
        $tOutputFile = Io::translatePath('{writable}cache/md/' . $uObject['compiledFile']);

        if (Framework::$disableCaches || !Io::isReadableAndNewer($tOutputFile, filemtime($tInputFile))) {
            if (is_null(self::$engine)) {
                self::$engine = new MarkdownExtraParser();
            }

            $tInput = Io::read($tInputFile);
            $tOutput = self::$engine->transformMarkdown($tInput);

            Io::writeSerialize($tOutputFile, $tOutput);
            echo $tOutput;
        } else {
            echo Io::readSerialize($tOutputFile);
        }
    }
}
