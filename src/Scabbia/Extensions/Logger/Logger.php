<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Logger;

use Scabbia\Events;
use Scabbia\Extensions\Helpers\String;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Io;
use Psr\Log\LogLevel;

/**
 * Logger Extension
 *
 * @package Scabbia
 * @subpackage Logger
 * @version 1.1.0
 *
 * @todo minimum severity
 * @todo datasources
 */
class Logger
{
    /**
     * @ignore
     */
    public static $instance = null;
    /**
     * @ignore
     */
    public static $filename;
    /**
     * @ignore
     */
    public static $line;
    /**
     * @var bool    Indicates the logger is currently disabled or not
     */
    public static $disabled = false;
    /**
     * @ignore
     */
    public static $typeCounts = array(
        'memory' => 0,
        'time' => 0,
        'error' => 0,
        'log' => 0,
        'query' => 0
    );
    /**
     * @ignore
     */
    public static $console = array();


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$filename = Config::get('logger/filename', '{date|\'d-m-Y\'}.txt');
        self::$line = Config::get(
            'logger/line',
            '[{date|\'d-m-Y H:i:s\'}] {strtoupper|@category} | {@ip} | {@location} | {@message}'
        );

        set_exception_handler('Scabbia\\Extensions\\Logger\\Logger::exceptionCallback');
        set_error_handler('Scabbia\\Extensions\\Logger\\Logger::errorCallback', E_ALL);
    }

    /**
     * @ignore
     */
    public static function errorCallback($uCode, $uMessage, $uFile, $uLine)
    {
        if (self::$disabled) {
            return;
        }

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
        if (self::$disabled) {
            return;
        }

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
                $tType = LogLevel::ERROR;
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $tType = LogLevel::WARNING;
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $tType = LogLevel::NOTICE;
                break;
            case E_STRICT:
                $tType = LogLevel::WARNING;
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $tType = LogLevel::WARNING;
                break;
            default:
                $tType = LogLevel::WARNING;
                break;
        }

        self::write(
            'HANDLER',
            $tType,
            array(
                'type' => 'error',
                'message' => $uMessage,
                'file' => $uFile,
                'line' => $uLine,
                'halt' => true
            )
        );
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $uClass
     * @param mixed $uLevel
     * @param array $uContext
     * @return null
     */
    public static function write($uClass, $uLevel, array $uContext = array())
    {
        if (!isset($uContext['type'])) {
            $uContext['type'] = ($uLevel == LogLevel::DEBUG || $uLevel == LogLevel::INFO) ? 'log' : 'error';
        }
        self::$typeCounts[$uContext['type']]++;

        $uContext['class'] = $uClass;
        $uContext['category'] = $uLevel;
        $uContext['ip'] = $_SERVER['REMOTE_ADDR'];
        if (isset($uContext['message'])) {
            $uContext['message'] = String::prefixLines($uContext['message'], '- ', PHP_EOL);
        }

        if (isset($uContext['file'])) {
            if (Framework::$development) {
                $uContext['location'] = Io::extractPath($uContext['file']);
                if (isset($uContext['line'])) {
                    $uContext['location'] .= ' @' . $uContext['line'];
                }
            } else {
                $uContext['location'] = pathinfo($uContext['file'], PATHINFO_FILENAME);
            }
        } else {
            $uContext['location'] = '-';
        }

        $uContext['stackTrace'] = array();
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
                if (Framework::$development) {
                    $tLocation = Io::extractPath($tFrame['file']);
                    if (isset($tFrame['line'])) {
                        $tLocation .= ' @' . $tFrame['line'];
                    }
                } else {
                    $tLocation = pathinfo($tFrame['file'], PATHINFO_FILENAME);
                }
            } else {
                $tLocation = '-';
            }

            $uContext['stackTrace'][] = $tFunction . '(' . implode(', ', $tArgs) . ') in ' . $tLocation;
        }

        $uContext['eventDepth'] = Events::$eventDepth;

        Events::$disabled = true;

        if (!Framework::$readonly) {
            $tContent = '+ ' . String::format(Logger::$line, $uContext);
            $tFilename = Io::translatePath('{writable}logs/' . String::format(Logger::$filename, $uContext), true);

            Io::write($tFilename, $tContent, LOCK_EX | FILE_APPEND);
        }

        self::$console[] = $uContext;

        Events::$disabled = false;

        if (isset($uContext['halt']) && $uContext['halt']) {
            Events::invoke('reportError', $uContext);
            Framework::end(-1);
        }
    }
}
