<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

/**
 * Delegate is an inline members which executes an event-chain execution similar to Events,
 * but designed for object-oriented architecture.
 *
 * @package Scabbia
 * @version 1.1.0
 */
class Delegate
{
    /**
     * @var array   List of callbacks
     */
    public $callbacks = null;


    /**
     * Constructs a new delegate in order to assign it to a member
     *
     * @return object a delegate
     */
    public static function assign()
    {
        $tNewInstance = new Delegate();

        return function ($uCallback = null, $uState = null, $uPriority = 10) use ($tNewInstance) {
            if (!is_null($uCallback)) {
                $tNewInstance->add($uCallback, $uState, $uPriority);
            }

            return $tNewInstance;
        };
    }

    /**
     * Adds a callback to delegate
     *
     * @param callback  $uCallback  callback method
     * @param mixed     $uState     state object
     * @param int       $uPriority  priority level
     */
    public function add($uCallback, $uState = null, $uPriority = 10)
    {
        if (is_null($this->callbacks)) {
            $this->callbacks = new \SplPriorityQueue();
        }

        $this->callbacks->insert(array($uCallback, $uState), $uPriority);
    }

    /**
     * Invokes the event-chain execution
     *
     * @return bool whether the execution is broken or not
     */
    public function invoke()
    {
        $tArgs = func_get_args();

        if (!is_null($this->callbacks)) {
            foreach ($this->callbacks as $tCallback) {
                $tEventArgs = $tArgs;
                array_unshift($tEventArgs, $tCallback[1]);

                if (call_user_func_array($tCallback[0], $tEventArgs) === false) {
                    return false;
                }
            }
        }

        return true;
    }
}
