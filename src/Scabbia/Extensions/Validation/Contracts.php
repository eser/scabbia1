<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Validation;

/**
 * Validation Extension: Contracts Class
 *
 * @package Scabbia
 * @subpackage Validation
 * @version 1.1.0
 */
class Contracts
{
    /**
     * @ignore
     */
    public $value;

    /**
     * @ignore
     */
    public static function __callStatic($uName, array $uArgs)
    {
        return new Contracts(call_user_func_array('Scabbia\\Extensions\\Validation\\Conditions::' . $uName, $uArgs));
    }

    /**
     * @ignore
     */
    public function __construct($uValue)
    {
        $this->value = $uValue;
    }

    /**
     * @ignore
     */
    public function __call($uName, array $uArgs)
    {
        if (strncmp($uName, 'or', 2) === 0) {
            $uNewName = lcfirst(substr($uName, 2));

            $this->value = $this->value || call_user_func_array(
                'Scabbia\\Extensions\\Validation\\Conditions::' . $uNewName,
                $uArgs
            );

            return $this;
        }

        if (strncmp($uName, 'and', 3) === 0) {
            $uNewName = lcfirst(substr($uName, 3));

            $this->value = $this->value && call_user_func_array(
                'Scabbia\\Extensions\\Validation\\Conditions::' . $uNewName,
                $uArgs
            );

            return $this;
        }

        // throw new \Exception('no method defined');
    }

    /**
     * @ignore
     *
     * @throws \Exception
     */
    public function exception($uErrorMessage)
    {
        if ($this->value !== false) {
            return;
        }

        throw new \Exception($uErrorMessage);
    }

    /**
     * @ignore
     */
    public function check()
    {
        return ($this->value !== false);
    }

    /**
     * @ignore
     */
    public function get()
    {
        return $this->value;
    }
}
