<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Logger;

use Scabbia\Extensions\Logger\LoggerInstance;
use Scabbia\Config;
use Psr\Log\LogLevel;

/**
 * Logger Extension
 *
 * @package Scabbia
 * @subpackage Logger
 * @version 1.1.0
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
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new LoggerInstance();
        }

        return self::$instance;
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

        self::getInstance()->log(
            $tType,
            $uMessage,
            array(
                'file' => $uFile,
                'line' => $uLine
            )
        );
    }
}
