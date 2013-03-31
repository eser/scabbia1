<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
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
     * @var array   The array of registered callbacks for the events
     */
    public static $events = array();
    /**
     * @var array   Event depth
     */
    public static $eventDepth = array();
    /**
     * @var bool    Indicates the event manager is currently disabled or not
     */
    public static $disabled = false;


    /**
     * Makes a callback method subscribed to specified event.
     *
     * @param string    $uEventName event
     * @param string    $uType      type of event
     * @param mixed     $uValue     value
     * @param int       $uPriority  priority
     */
    public static function register($uEventName, $uType, $uValue, $uPriority = 10)
    {
        if (!isset(self::$events[$uEventName])) {
            self::$events[$uEventName] = new Delegate();
        }

        self::$events[$uEventName]->add('Scabbia\\Events::invokeSingle', array($uType, $uValue), $uPriority);
    }

    /**
     * Invokes an event.
     *
     * @param string    $uEventName name of the event
     * @param array     $uEventArgs arguments for the event
     *
     * @uses invokeSingle()
     * @return bool whether the event is invoked or not
     */
    public static function invoke($uEventName, $uEventArgs = null)
    {
        if (self::$disabled) {
            return null;
        }

        if (!isset(self::$events[$uEventName])) {
            return null;
        }

        return self::$events[$uEventName]->invoke($uEventArgs);
    }

    /**
     * Executes a single event.
     *
     * @param string    $uState     state object
     * @param mixed     $uEventArgs arguments for the event
     *
     * @return bool whether the event is invoked or not
     */
    public static function invokeSingle($uState, $uEventArgs = null)
    {
        // if (self::$disabled) {
        //    return null;
        // }

        switch ($uState[0]) {
            case 'loadClass':
                class_exists($uState[1], true);
                break;
            case 'include':
                include Io::translatePath($uState[1]);
                break;
            case 'callback':
                if (is_array($uState[1])) {
                    array_push(self::$eventDepth, get_class($uState[1][0]) . '::' . $uState[1][1] . '()');
                } else {
                    array_push(self::$eventDepth, '\\' . $uState[1] . '()');
                }

                $tReturn = call_user_func($uState[1], $uEventArgs);

                array_pop(self::$eventDepth);

                if ($tReturn === false) {
                    return false;
                }
                break;
        }

        return true;
    }
}
