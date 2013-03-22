<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
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
     * List of callbacks
     */
    public $callbacks = array();
    /**
     * Is priority sort needed or not?
     */
    public $prioritySortNeeded = false;


    /**
     * Constructs a new delegate in order to assign it to a member
     *
     * @return object
     */
    public static function assign()
    {
        $tNewInstance = new Delegate();

        return function($uCallback = null, array $uState = null, $uPriority = 10) use ($tNewInstance) {
            if (!is_null($uCallback)) {
                $tNewInstance->add($uCallback, $uState, $uPriority);
            }

            return $tNewInstance;
        };
    }

    /**
     * Adds
     */
    public function add($uCallback, array $uState = null, $uPriority = 10) {
        $this->callbacks[] = array($uCallback, $uState, $uPriority);
        $this->prioritySortNeeded = true;
    }

    /**
     * Invokes the event-chain execution
     */
    public function invoke() {
        $tArgs = func_get_args();

        if ($this->prioritySortNeeded) {
            usort(
                $this->callbacks,
                function ($uFirst, $uSecond) {
                    if ($uFirst[2] == $uSecond[2]) {
                        return 0;
                    }

                    return ($uFirst[2] > $uSecond[2]) ? 1 : -1;
                }
            );

            $this->prioritySortNeeded = false;
        }

        foreach ($this->callbacks as $tCallback) {
            $tEventArgs = (!is_null($tCallback[1]) ? array_merge($tCallback[1], $tArgs) : $tArgs);

            if (call_user_func_array($tCallback[0], $tEventArgs) === false) {
                return false;
            }
        }

        return true;
    }
}
