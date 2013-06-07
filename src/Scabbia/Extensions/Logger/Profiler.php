<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Logger;

use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions;

/**
 * Logger Extension: Profiler Class
 *
 * @package Scabbia
 * @subpackage Logger
 * @version 1.1.0
 */
class Profiler
{
    /**
     * @ignore
     */
    public static $markers = array();
    /**
     * @ignore
     */
    public static $stack = array();


    /**
     * @ignore
     */
    public static function start($uName, array $uParameters = array())
    {
        /*
        if (Utils::phpVersion('5.3.6')) {
            $tBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        } else {
            $tBacktrace = debug_backtrace(false);
        }
        */
        $tBacktrace = debug_backtrace();

        $tLast = current($tBacktrace);
        $tProfileData = $uParameters + array(
            'name' => $uName,
            'file' => $tLast['file'],
            'line' => $tLast['line'],
            'startTime' => microtime(true),
            'startMemory' => memory_get_peak_usage()
        );

        self::$stack[] = $tProfileData;
    }

    /**
     * @ignore
     */
    public static function stop($uExtraParameters = null)
    {
        $tProfileData = array_pop(self::$stack);

        if (is_null($tProfileData)) {
            return false;
        }

        $tProfileData['consumedTime'] = microtime(true) - $tProfileData['startTime'];
        $tProfileData['consumedMemory'] = memory_get_peak_usage() - $tProfileData['startMemory'];

        if (!is_null($uExtraParameters)) {
            $tProfileData += $uExtraParameters;
        }

        if (!isset(self::$markers[$tProfileData['name']])) {
            self::$markers[$tProfileData['name']] = array($tProfileData);
        } else {
            self::$markers[$tProfileData['name']][] = $tProfileData;
        }

        return $tProfileData;
    }

    /**
     * @ignore
     */
    public static function clear()
    {
        while (count(self::$stack) > 0) {
            self::stop();
        }
    }

    /**
     * @ignore
     */
    public static function get($uName)
    {
        return self::$markers[$uName];
    }

    /**
     * @ignore
     */
    public static function export($tOutput = true)
    {
        return String::vardump(self::$markers, $tOutput);
    }

    /**
     * @ignore
     */
    public static function exportStack($tOutput = true)
    {
        return String::vardump(self::$stack, $tOutput);
    }
}
