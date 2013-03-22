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

        return function($uCallback = null, $uPriority = 10) use ($tNewInstance) {
            if (!is_null($uCallback)) {
                $tNewInstance->add($uCallback, $uPriority);
            }

            return $tNewInstance;
        };
    }

    /**
     * Adds
     */
    public function add($uCallback, $uPriority = 10) {
        $this->callbacks[] = array($uCallback, $uPriority);
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
                    if ($uFirst[1] == $uSecond[1]) {
                        return 0;
                    }

                    return ($uFirst[1] > $uSecond[1]) ? 1 : -1;
                }
            );

            $this->prioritySortNeeded = false;
        }

        foreach ($this->callbacks as $tCallback) {
            call_user_func_array($tCallback[0], $tArgs);
        }
    }
}
