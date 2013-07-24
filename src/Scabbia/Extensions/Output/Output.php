<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Output;

/**
 * Output Extension
 *
 * @package Scabbia
 * @subpackage Output
 * @version 1.1.0
 *
 * @todo application postfilter
 * @todo fragment methods and caching with fragments
 */
class Output
{
    /**
     * @ignore
     */
    public static $effectList = array();


    /**
     * @ignore
     */
    public static function begin()
    {
        ob_start('Scabbia\\Extensions\\Output\\Output::flushOutput');
        ob_implicit_flush(false);

        $tArgs = func_get_args();
        self::$effectList[] = $tArgs;
    }

    /**
     * @ignore
     */
    public static function end($uFlush = true)
    {
        $tContent = ob_get_clean();

        foreach (array_pop(self::$effectList) as $tEffect) {
            $tContent = call_user_func($tEffect, $tContent);
        }

        if ($uFlush) {
            echo $tContent;
        }

        return $tContent;
    }

    /**
     * @ignore
     */
    public static function flushOutput($uContent)
    {
        return "";
    }
}
