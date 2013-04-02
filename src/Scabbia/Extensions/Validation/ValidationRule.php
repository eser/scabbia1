<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Validation;

/**
 * Validation Extension: ValidationRule Class
 *
 * @package Scabbia
 * @subpackage Validation
 * @version 1.1.0
 */
class ValidationRule
{
    /**
     * @ignore
     */
    public $field;
    /**
     * @ignore
     */
    public $conditions = array();
    /**
     * @ignore
     */
    public $errorMessage;


    /**
     * @ignore
     */
    public function __construct($uField)
    {
        $this->field = $uField;
    }

    /**
     * @ignore
     */
    public function __call($uName, array $uArgs)
    {
        return $this->add($uName, $uArgs);
    }

    /**
     * @ignore
     */
    public function add($uType, array $uArgs)
    {
        if (strncmp($uType, 'or', 2) == 0) {
            $uNewType = lcfirst(substr($uType, 2));
            $this->conditions[] = array($uNewType, $uArgs, 'or');

            return $this;
        }

        if (strncmp($uType, 'and', 3) == 0) {
            $uNewType = lcfirst(substr($uType, 3));
            $this->conditions[] = array($uNewType, $uArgs, 'and');

            return $this;
        }

        $this->conditions[] = array($uType, $uArgs, null);

        return $this;
    }

    /**
     * @ignore
     */
    public function errorMessage($uErrorMessage)
    {
        $this->errorMessage = $uErrorMessage;

        return $this;
    }
}
