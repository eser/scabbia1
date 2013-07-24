<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Unittest;

/**
 * Unittest Extension
 *
 * @package Scabbia
 * @subpackage Unittest
 * @version 1.1.0
 */
class Unittest
{
    /**
     * @ignore
     */
    public static function testClass($uClass)
    {
        $tInstance = new $uClass ();
        $tInstance->test();

        return $tInstance;
    }
}
