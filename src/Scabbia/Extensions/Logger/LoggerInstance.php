<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Logger;

use Scabbia\Extensions\Logger\Logger;
use Scabbia\Extensions\String\String;
use Scabbia\Events;
use Scabbia\Framework;
use Scabbia\Io;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Logger Extension: LoggerInstance Class
 *
 * @package Scabbia
 * @subpackage Logger
 * @version 1.1.0
 */
class LoggerInstance implements LoggerInterface
{
    /**
     * System is unusable.
     *
     * @param string $uMessage
     * @param array $uContext
     * @return null
     */
    public function emergency($uMessage, array $uContext = array())
    {
        $this->log(LogLevel::EMERGENCY, $uMessage, $uContext);
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
        $this->log(LogLevel::ALERT, $uMessage, $uContext);
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
        $this->log(LogLevel::CRITICAL, $uMessage, $uContext);
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
        $this->log(LogLevel::ERROR, $uMessage, $uContext);
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
        $this->log(LogLevel::WARNING, $uMessage, $uContext);
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
        $this->log(LogLevel::NOTICE, $uMessage, $uContext);
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
        $this->log(LogLevel::INFO, $uMessage, $uContext);
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
        $this->log(LogLevel::DEBUG, $uMessage, $uContext);
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
        $uContext['category'] = $uLevel;
        $uContext['ip'] = $_SERVER['REMOTE_ADDR'];
        $uContext['message'] = String::prefixLines($uMessage, '- ', PHP_EOL);

        if (isset($uContext['file'])) {
            if (Framework::$development >= 1) {
                $uContext['location'] = Io::extractPath($uContext['file']);
                if (isset($uContext['line'])) {
                    $uContext['location'] .= ' @' . $uContext['line'];
                }
            } else {
                $uContext['location'] = pathinfo($uContext['file'], PATHINFO_FILENAME);
            }
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
                if (Framework::$development >= 1) {
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

        $tIgnoreError = false;
        $uContext['ignore'] = &$tIgnoreError;

        Events::invoke('reportError', $uContext);

        if (!$tIgnoreError) {
            Events::$disabled = true;

            if (!Framework::$readonly) {
                $tContent = '+ ' . String::format(Logger::$line, $uContext);
                $tFilename = Io::writablePath('logs/' . String::format(Logger::$filename, $uContext), true);

                file_put_contents($tFilename, $tContent, FILE_APPEND);
            }

            exit();
        }
    }
}
