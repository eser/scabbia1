<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Logger;

use Scabbia\Extensions\String\String;
use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Framework;
use Scabbia\Utils;

/**
 * Logger Extension
 *
 * @package Scabbia
 * @subpackage Logger
 * @version 1.1.0
 *
 * @todo PSR-3 Logger Interface Compability
 */
class Logger
{
    /**
     * @ignore
     */
    public static $filename;
    /**
     * @ignore
     */
    public static $line;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$filename = Config::get('logger/filename', '{date|\'d-m-Y\'}.txt');
        self::$line = Config::get('logger/line', '[{date|\'d-m-Y H:i:s\'}] {strtoupper|@category} | {@ip} | {@location} | {@message}');

        set_exception_handler('Scabbia\\Extensions\\Logger\\logger::exceptionCallback');
        set_error_handler('Scabbia\\Extensions\\Logger\\logger::errorCallback', E_ALL);
    }

    /**
     * @ignore
     */
    public static function errorCallback($uCode, $uMessage, $uFile, $uLine)
    {
        self::handler(
            $uMessage,
            $uCode,
            $uFile,
            $uLine
        );
    }

    /**
     * @ignore
     */
    public static function exceptionCallback($uException)
    {
        self::handler(
            $uException->getMessage(),
            $uException->getCode(),
            $uException->getFile(),
            $uException->getLine()
        );
    }

    /**
     * @ignore
     */
    public static function handler($uMessage, $uCode, $uFile, $uLine)
    {
        switch ($uCode) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                $tType = 'Error';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $tType = 'Warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $tType = 'Notice';
                break;
            case E_STRICT:
                $tType = 'Strict';
                break;
                // case E_DEPRECATED: // PHP >= 5.3.0
            case 8192:
                // case E_USER_DEPRECATED: // PHP >= 5.3.0
            case 16384:
                break;
            default:
                $tType = 'Unknown';
                break;
        }

        if (Framework::$development >= 1) {
            $tLocation = Utils::extractPath($uFile) . ' @' . $uLine;
        } else {
            $tLocation = pathinfo($uFile, PATHINFO_FILENAME);
        }

        $tStackTrace = array();
        foreach (array_slice(debug_backtrace(), 2) as $tFrame) {
            $tArgs = array();
            /*
            if (isset($tFrame['args'])) {
                foreach ($tFrame['args'] as $tArg) {
                    $tArgs[] = var_export($tArg, true);
                }
            }
            */

            if (isset($tFrame['class'])) {
                $tFunction = $tFrame['class'] . $tFrame['type'] . $tFrame['function'];
            } else {
                $tFunction = $tFrame['function'];
            }

            if (isset($tFrame['file'])) {
                if (Framework::$development >= 1) {
                    $tLocation = Utils::extractPath($tFrame['file']) . ' @' . $tFrame['line'];
                } else {
                    $tLocation = pathinfo($tFrame['file'], PATHINFO_FILENAME);
                }
            } else {
                $tLocation = '-';
            }

            $tStackTrace[] = $tFunction . '(' . implode(', ', $tArgs) . ') in ' . $tLocation;
        }

        $tIgnoreError = false;
        $tParms = array(
            'type' => &$tType,
            'message' => $uMessage,
            'location' => $tLocation,
            'stackTrace' => $tStackTrace,
            'eventDepth' => Events::$eventDepth,
            'ignore' => &$tIgnoreError
        );
        Events::invoke('reportError', $tParms);

        if (!$tIgnoreError) {
            Events::$disabled = true;
            self::write('error', $tParms);
            exit();
        }
    }

    /**
     * @ignore
     */
    public static function write($uCategory, $uParams)
    {
        $uParams['category'] = $uCategory;
        $uParams['ip'] = $_SERVER['REMOTE_ADDR'];

        $uParams['message'] = String::prefixLines($uParams['message'], '- ', PHP_EOL);
        $tContent = '+ ' . String::format(self::$line, $uParams);

        if (!Framework::$readonly) {
            $tFilename = Utils::writablePath('logs/' . String::format(self::$filename, $uParams), true);
            file_put_contents($tFilename, $tContent, FILE_APPEND);
        }
    }
}
