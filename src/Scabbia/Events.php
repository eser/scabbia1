<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Delegate;
use Scabbia\Io;

/**
 * Event manager which handles communication between framework parts and extensions.
 *
 * @package Scabbia
 * @version 1.1.0
 */
class Events
{
    /**
     * The array of registered callbacks for the events
     */
    public static $events = array();
    /**
     * Event depth
     */
    public static $eventDepth = array();
    /**
     * Indicates the event manager is currently disabled or not
     */
    public static $disabled = false;


    /**
     * Makes a callback method subscribed to specified event.
     *
     * @param string $uEventName the event
     * @param string $uType the type of event
     * @param mixed $uValue the value
     * @param int $uPriority the priority
     */
    public static function register($uEventName, $uType, $uValue, $uPriority = 10)
    {
        if (!array_key_exists($uEventName, self::$events)) {
            self::$events[$uEventName] = new Delegate();
        }

        self::$events[$uEventName]->add('Scabbia\\Events::invokeSingle', array($uType, $uValue), $uPriority);
    }

    /**
     * Invokes an event.
     *
     * @param string $uEventName the event
     * @param array $uEventArgs arguments for the event
     *
     * @uses invokeSingle()
     * @return bool whether the event is invoked or not
     */
    public static function invoke($uEventName, array $uEventArgs = array())
    {
        if (self::$disabled) {
            return null;
        }

        if (!array_key_exists($uEventName, self::$events)) {
            return null;
        }

        return self::$events[$uEventName]->invoke($uEventArgs);
    }

    /**
     * Executes a single event.
     *
     * @param string $uType the type of event
     * @param mixed $uValue the value of event
     * @param array $uEventArgs arguments for the event
     *
     * @return bool whether the event is invoked or not
     */
    public static function invokeSingle($uType, $uValue, array $uEventArgs = null)
    {
        // if (self::$disabled) {
        //    return null;
        // }

        switch ($uType) {
            case 'loadClass':
                class_exists($uValue, true);
                break;
            case 'include':
                include Io::translatePath($uValue);
                break;
            case 'callback':
                if (is_array($uValue)) {
                    array_push(self::$eventDepth, get_class($uValue[0]) . '::' . $uValue[1] . '()');
                } else {
                    array_push(self::$eventDepth, '\\' . $uValue . '()');
                }

                if (!is_null($uEventArgs)) {
                    $tReturn = call_user_func_array($uValue, $uEventArgs);
                } else {
                    $tReturn = call_user_func($uValue);
                }
                array_pop(self::$eventDepth);

                if ($tReturn === false) {
                    return false;
                }
                break;
        }

        return true;
    }
}
