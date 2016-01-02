<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Io;
use Michelf\MarkdownExtra;

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
            if (self::$engine === null) {
                self::$engine = new MarkdownExtra();
            }

            $tInput = Io::read($tInputFile);
            $tOutput = self::$engine->transform($tInput);

            Io::writeSerialize($tOutputFile, $tOutput);
            echo $tOutput;
        } else {
            echo Io::readSerialize($tOutputFile);
        }
    }
}
