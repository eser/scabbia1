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
     * @var mixed   Expected return value for interruption
     */
    public $expectedReturn;


    /**
     * Constructs a new delegate in order to assign it to a member
     *
     * @param mixed $uExpectedReturn Expected return value for interruption
     *
     * @return object a delegate
     */
    public static function assign($uExpectedReturn = false)
    {
        $tNewInstance = new Delegate($uExpectedReturn);

        return function (/* callable */ $uCallback = null, $uState = null, $uPriority = 10) use ($tNewInstance) {
            if ($uCallback !== null) {
                $tNewInstance->add($uCallback, $uState, $uPriority);
            }

            return $tNewInstance;
        };
    }

    /**
     * Constructs a new instance of delegate.
     *
     * @param mixed $uExpectedReturn Expected return value for interruption
     */
    public function __construct($uExpectedReturn = false)
    {
        $this->expectedReturn = $uExpectedReturn;
    }

    /**
     * Adds a callback to delegate
     *
     * @param callback  $uCallback  callback method
     * @param mixed     $uState     state object
     * @param int       $uPriority  priority level
     */
    public function add(/* callable */ $uCallback, $uState = null, $uPriority = 10)
    {
        if ($this->callbacks === null) {
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

        if ($this->callbacks !== null) {
            foreach ($this->callbacks as $tCallback) {
                $tEventArgs = $tArgs;
                array_unshift($tEventArgs, $tCallback[1]);

                if (call_user_func_array($tCallback[0], $tEventArgs) === $this->expectedReturn) {
                    return false;
                }
            }
        }

        return true;
    }
}
