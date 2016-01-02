<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
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
