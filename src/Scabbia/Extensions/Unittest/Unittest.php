<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Unittest;

use Scabbia\Extensions\String\String;
use Scabbia\Extensions;

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
    public static $stack = array();
    /**
     * @ignore
     */
    public static $report = array();


    /**
     * @ignore
     */
    public static function beginClass($uClass)
    {
        $tMethods = get_class_methods($uClass);

        $tInstance = new $uClass ();
        foreach ($tMethods as $tMethod) {
            self::begin($uClass . '->' . $tMethod . '()', array(&$tInstance, $tMethod));
        }
    }

    /**
     * @ignore
     */
    public static function begin($uName, $uCallback)
    {
        array_push(self::$stack, array('name' => $uName, 'callback' => $uCallback));
        call_user_func($uCallback);
        array_pop(self::$stack);
    }

    /**
     * @ignore
     */
    private static function addReport($uOperation, $uIsFailed)
    {
        $tScope = end(self::$stack);

        if (!isset(self::$report[$tScope['name']])) {
            self::$report[$tScope['name']] = array();
        }

        self::$report[$tScope['name']][] = array(
            'operation' => $uOperation,
            'failed' => $uIsFailed
        );
    }

    /**
     * @ignore
     */
    public static function assertTrue($uCondition)
    {
        if ($uCondition) {
            self::addReport('assertTrue', true);

            return;
        }

        self::addReport('assertTrue', false);
    }

    /**
     * @ignore
     */
    public static function assertFalse($uCondition)
    {
        if (!$uCondition) {
            self::addReport('assertFalse', true);

            return;
        }

        self::addReport('assertFalse', false);
    }

    /**
     * @ignore
     */
    public static function assertNull($uVariable)
    {
        if (is_null($uVariable)) {
            self::addReport('assertNull', true);

            return;
        }

        self::addReport('assertNull', false);
    }

    /**
     * @ignore
     */
    public static function assertNotNull($uVariable)
    {
        if (!is_null($uVariable)) {
            self::addReport('assertNotNull', true);

            return;
        }

        self::addReport('assertNotNull', false);
    }

    /**
     * @ignore
     */
    public static function export($tOutput = true)
    {
        return String::vardump(self::$report, $tOutput);
    }
}
