<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Logger;

use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Logger\Logger;
use Scabbia\Extensions\Views\Views;
use Scabbia\Framework;

/**
 * Logger Extension: QuickProfiler Class
 *
 * @package Scabbia
 * @subpackage Logger
 * @version 1.1.0
 */
class QuickProfiler
{
    /**
     * @ignore
     */
    public static $output = array();


    /**
     * @ignore
     */
    public static function init()
    {
        self::gatherConsoleData();
        self::gatherFileData();
        self::gatherMemoryData();
        self::gatherQueryData();
        self::gatherTimeData();

        Views::viewFile('{core}views/pqp/display.php', self::$output);
    }

    /**
     * @ignore
     */
    public static function gatherConsoleData()
    {
        self::$output['logcounts'] = Logger::$typeCounts;
        self::$output['logcount'] = count(Logger::$console);
        self::$output['logs'] = Logger::$console;
    }

    /**
     * @ignore
     */
    public static function gatherFileData()
    {
        self::$output['files'] = array();

        $tTotalSize = 0;
        $tLargestSize = 0;
        foreach (get_included_files() as $tFile) {
            $tFileSize = filesize($tFile);
            $tTotalSize += $tFileSize;

            self::$output['files'][] = array(
                'message' => $tFile,
                'size' => String::sizeCalc($tFileSize)
            );
            if ($tFileSize > $tLargestSize) {
                $tLargestSize = $tFileSize;
            }
        }

        self::$output['fileTotals'] = array(
            'count' => count(self::$output['files']),
            'size' => String::sizeCalc($tTotalSize),
            'largest' => String::sizeCalc($tLargestSize)
        );
    }

    /**
     * @ignore
     */
    public static function gatherMemoryData()
    {
        self::$output['memoryTotals'] = array(
            'used' => String::sizeCalc(memory_get_peak_usage()),
            'total' => ini_get('memory_limit')
        );
    }

    /**
     * @ignore
     */
    public static function gatherQueryData()
    {
        self::$output['queryTotals'] = array(
            'time' => 0
        );

        foreach (Logger::$console as $tLog) {
            if (isset($tLog['type']) && $tLog['type'] === 'query') {
                self::$output['queryTotals']['time'] += $tLog['consumedTime'];
            }
        }
    }

    /**
     * @ignore
     */
    public static function gatherTimeData()
    {
        self::$output['timeTotals'] = array(
            'total' => String::timeCalc(microtime(true) - Framework::$timestamp),
            'allowed' => ini_get('max_execution_time')
        );
    }
}
