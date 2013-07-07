<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Logger;

use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Logger\Logger;
use Scabbia\Extensions\Views\Views;
use Scabbia\Framework;

/**
 * Logger Extension: PQP Class
 *
 * @package Scabbia
 * @subpackage Logger
 * @version 1.1.0
 */
class PQP
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

    /*-------------------------------------------
	     FORMAT THE DIFFERENT TYPES OF LOGS
	-------------------------------------------*/

    public static function gatherConsoleData() {
        self::$output['logcounts'] = Logger::$typeCounts;
        self::$output['logcount'] = count(Logger::$console);
        self::$output['logs'] = Logger::$console;
    }

    /*-------------------------------------------
        AGGREGATE DATA ON THE FILES INCLUDED
    -------------------------------------------*/

    public static function gatherFileData() {
        $files = get_included_files();
        $fileList = array();
        $fileTotals = array(
            'count' => count($files),
            'size' => 0,
            'largest' => 0,
        );

        foreach ($files as $key => $file) {
            $size = filesize($file);
            $fileList[] = array(
                'message' => $file,
                'size' => String::sizeCalc($size)
            );
            $fileTotals['size'] += $size;
            if ($size > $fileTotals['largest']) {
                $fileTotals['largest'] = $size;
            }
        }

        $fileTotals['size'] = String::sizeCalc($fileTotals['size']);
        $fileTotals['largest'] = String::sizeCalc($fileTotals['largest']);
        self::$output['files'] = $fileList;
        self::$output['fileTotals'] = $fileTotals;
    }

    /*-------------------------------------------
	     MEMORY USAGE AND MEMORY AVAILABLE
	-------------------------------------------*/

    public static function gatherMemoryData() {
        $memoryTotals = array();
        $memoryTotals['used'] = String::sizeCalc(memory_get_peak_usage());
        $memoryTotals['total'] = ini_get('memory_limit');
        self::$output['memoryTotals'] = $memoryTotals;
    }

    /*--------------------------------------------------------
	     QUERY DATA -- DATABASE OBJECT WITH LOGGING REQUIRED
	----------------------------------------------------------*/

    public static function gatherQueryData() {
        $queryTotals = array();
        $queryTotals['time'] = 0;

        foreach (Logger::$console as $tLog) {
            if (isset($tLog['type']) && $tLog['type'] === 'query') {
                $queryTotals['time'] += $tLog['consumedTime'];
            }
        }

        self::$output['queryTotals'] = $queryTotals;
    }

    /*-------------------------------------------
	     TIME DATA FOR ENTIRE PAGE LOAD
	-------------------------------------------*/

    public static function gatherTimeData() {
        $timeTotals = array();
        $timeTotals['total'] = String::timeCalc(microtime(true) - Framework::$timestamp);
        $timeTotals['allowed'] = ini_get('max_execution_time');
        self::$output['timeTotals'] = $timeTotals;
    }
}
