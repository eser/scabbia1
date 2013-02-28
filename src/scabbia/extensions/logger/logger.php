<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Logger;

use Scabbia\Extensions\Profiler\Profiler;
use Scabbia\Extensions\String\String;
use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Extensions;
use Scabbia\Framework;
use Scabbia\Utils;

/**
 * Logger Extension
 *
 * @package Scabbia
 * @subpackage logger
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends string
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
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
        self::$line = Config::get('logger/line', '[{date|\'d-m-Y H:i:s\'}] {strtoupper|@category} | {@ip} | {@message}');

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

        $tIgnoreError = false;
        $tParms = array(
            'type' => &$tType,
            'message' => $uMessage,
            'file' => $uFile,
            'line' => $uLine,
            'ignore' => &$tIgnoreError
        );
        Events::invoke('reportError', $tParms);

        if (!$tIgnoreError) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            header('Content-Type: text/html, charset=UTF-8', true);

            Events::$disabled = true;
            $tEventDepth = Events::$eventDepth;

            for ($tCount = ob_get_level(); --$tCount > 1; ob_end_flush()) {
                ;
            }

            if (Framework::$development >= 1) {
                $tDeveloperLocation = $uFile . ' @' . $uLine;
            } else {
                $tDeveloperLocation = pathinfo($uFile, PATHINFO_FILENAME);
            }

            $tString = '';
            $tString .= '<pre style="font-family: \'Consolas\', monospace;">'; // for content-type: text/xml
            $tString .= '<div style="font-size: 11pt; color: #000060; border-bottom: 1px solid #C0C0C0; background: #F0F0F0; padding: 8px 12px 8px 12px;"><span style="font-weight: bold;">' . $tType . '</span>: ' . $tDeveloperLocation . '</div>' . PHP_EOL;
            $tString .= '<div style="font-size: 10pt; color: #404040; padding: 0px 12px 0px 12px; line-height: 20px;">' . $uMessage . '</div>' . PHP_EOL . PHP_EOL;

            if (Framework::$development >= 1) {
                if (count($tEventDepth) > 0) {
                    $tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; line-height: 20px;"><b>eventDepth:</b>' . PHP_EOL . implode(PHP_EOL, $tEventDepth) . '</div>' . PHP_EOL . PHP_EOL;
                }

                $tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; line-height: 20px;"><b>stackTrace:</b>' . PHP_EOL;

                $tCount = 0;
                foreach (array_slice(debug_backtrace(), 2) as $tFrame) {
                    $tArgs = array();
                    if (isset($tFrame['args'])) {
                        /*foreach ($tFrame['args'] as $tArg) {
                            $tArgs[] = var_export($tArg, true);
                        }*/
                    }

                    if (isset($tFrame['class'])) {
                        $tFunction = $tFrame['class'] . $tFrame['type'] . $tFrame['function'];
                    } else {
                        $tFunction = $tFrame['function'];
                    }

                    ++$tCount;
                    if (isset($tFrame['file'])) {
                        $tString .= '#' . $tCount . ' ' . $tFrame['file'] . '(' . $tFrame['line'] . '):' . PHP_EOL;
                    }

                    $tString .= '#' . $tCount . ' <strong>' . $tFunction . '</strong>(' . implode(', ', $tArgs) . ')' . PHP_EOL . PHP_EOL;
                }

                $tString .= '</div>' . PHP_EOL;

                $tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; line-height: 20px;"><b>profiler stack:</b>' . PHP_EOL;
                $tString .= Profiler::exportStack(false);
                $tString .= '</div>' . PHP_EOL;

                $tString .= '<div style="font-size: 10pt; color: #800000; padding: 0px 12px 0px 12px; line-height: 20px;"><b>profiler output:</b>' . PHP_EOL;
                $tString .= Profiler::export(false);
                $tString .= '</div>';
            }

            $tString .= '</pre>';

            self::write('error', array('message' => strip_tags($tString)));

            $tString .= '<div style="font-size: 7pt; color: #808080; padding: 0px 12px 0px 12px;">Generated by <a href="https://github.com/larukedi/Scabbia-Framework/">Scabbia Framework</a>.</div>' . PHP_EOL;
            echo $tString;

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
