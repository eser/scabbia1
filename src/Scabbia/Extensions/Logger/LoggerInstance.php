<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Logger;

use Scabbia\Extensions\Logger\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Logger Extension: LoggerInstance Class
 *
 * @package Scabbia
 * @subpackage Logger
 * @version 1.1.0
 *
 * @todo use datasources to write log messages
 */
class LoggerInstance implements LoggerInterface
{
    /**
     * @var string
     */
    public $className;
    /**
     * @ignore
     */
    public $profilerStack = array();


    /**
     * Initializes a new LoggerInstance class.
     *
     * @param string $uClassName
     */
    public function __construct($uClassName)
    {
        $this->className = $uClassName;
    }

    /**
     * System is unusable.
     *
     * @param string $uMessage
     * @param array $uContext
     * @return null
     */
    public function emergency($uMessage, array $uContext = array())
    {
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::EMERGENCY, $uContext);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $uMessage
     * @param array $uContext
     * @return null
     */
    public function alert($uMessage, array $uContext = array())
    {
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::ALERT, $uContext);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $uMessage
     * @param array $uContext
     * @return null
     */
    public function critical($uMessage, array $uContext = array())
    {
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::CRITICAL, $uContext);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $uMessage
     * @param array $uContext
     * @return null
     */
    public function error($uMessage, array $uContext = array())
    {
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::ERROR, $uContext);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $uMessage
     * @param array $uContext
     * @return null
     */
    public function warning($uMessage, array $uContext = array())
    {
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::WARNING, $uContext);
    }

    /**
     * Normal but significant events.
     *
     * @param string $uMessage
     * @param array $uContext
     * @return null
     */
    public function notice($uMessage, array $uContext = array())
    {
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::NOTICE, $uContext);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $uMessage
     * @param array $uContext
     * @return null
     */
    public function info($uMessage, array $uContext = array())
    {
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::INFO, $uContext);
    }

    /**
     * Detailed debug information.
     *
     * @param string $uMessage
     * @param array $uContext
     * @return null
     */
    public function debug($uMessage, array $uContext = array())
    {
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::DEBUG, $uContext);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $uLevel
     * @param string $uMessage
     * @param array $uContext
     * @return null
     */
    public function log($uLevel, $uMessage, array $uContext = array())
    {
        $uContext['message'] = $uMessage;
        Logger::write($this->className, $uLevel, $uContext);
    }

    /**
     * Logs total memory usage
     *
     * @param mixed $uMessage
     * @param int $uAmount
     * @param array $uContext
     * @return null
     */
    public function logMemory($uMessage, $uAmount, array $uContext = array())
    {
        $uContext['type'] = 'memory';
        $uContext['data'] = $uAmount;
        $uContext['datatype'] = 'log';
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::DEBUG, $uContext);
    }

    /**
     * Logs an object's memory usage
     *
     * @param mixed $uMessage
     * @param mixed $uObject
     * @param array $uContext
     * @return null
     */
    public function logMemoryObject($uMessage, $uObject, array $uContext = array())
    {
        $tSize = memory_get_usage();
        $uContext['object'] = unserialize(serialize($uObject));
        $uContext['data'] = memory_get_usage() - $tSize;

        $uContext['type'] = 'memory';
        $uContext['datatype'] = gettype($uObject);
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::DEBUG, $uContext);
    }

    /**
     * Logs a time snap
     *
     * @param mixed $uMessage
     * @param double $uTime
     * @param array $uContext
     * @return null
     */
    public function logTime($uMessage, $uTime, array $uContext = array())
    {
        $uContext['type'] = 'time';
        $uContext['data'] = $uTime;
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::DEBUG, $uContext);
    }

    /**
     * Logs a query
     *
     * @param mixed $uMessage
     * @param array $uContext
     * @return null
     */
    public function logQuery($uMessage, array $uContext = array())
    {
        $uContext['type'] = 'query';
        $uContext['message'] = $uMessage;
        Logger::write($this->className, LogLevel::DEBUG, $uContext);
    }

    /**
     * @ignore
     */
    public function profilerStart($uMessage, $uType = 'memory,time', array $uParameters = array())
    {
        /*
        if (Utils::phpVersion('5.3.6')) {
            $tBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        } else {
            $tBacktrace = debug_backtrace(false);
        }
        */
        // $tBacktrace = debug_backtrace();

        // $tLast = current($tBacktrace);
        $tProfileData = $uParameters + array(
            'type' => $uType,
            'message' => $uMessage,
            // 'file' => $tLast['file'],
            // 'line' => $tLast['line'],
            'startTime' => microtime(true),
            'startMemory' => memory_get_usage()
        );

        $this->profilerStack[] = $tProfileData;
    }

    /**
     * @ignore
     */
    public function profilerStop($uExtraParameters = null)
    {
        $tProfileData = array_pop($this->profilerStack);

        if ($tProfileData === null) {
            return false;
        }

        $tProfileData['consumedTime'] = microtime(true) - $tProfileData['startTime'];
        $tProfileData['consumedMemory'] = memory_get_usage() - $tProfileData['startMemory'];

        if ($uExtraParameters !== null) {
            $tProfileData += $uExtraParameters;
        }

        $tTypes = explode(',', $tProfileData['type']);

        if (in_array('time', $tTypes)) {
            $this->logTime($tProfileData['message'], $tProfileData['consumedTime']);
        }
        if (in_array('memory', $tTypes)) {
            $this->logMemory($tProfileData['message'], $tProfileData['consumedMemory']);
        }
        if (in_array('query', $tTypes)) {
            $this->logQuery($tProfileData['message'], $tProfileData);
        }

        return $tProfileData;
    }
}
