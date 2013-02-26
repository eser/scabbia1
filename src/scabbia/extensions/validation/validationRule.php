<?php

namespace Scabbia\Extensions\Validation;

/**
 * Validation Rule Class
 *
 * @package Scabbia
 * @subpackage ExtensibilityExtensions
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
    public $type;
    /**
     * @ignore
     */
    public $args;
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
    public function __call($uName, $uArgs)
    {
        $this->type = $uName;
        $this->args = $uArgs;

        return $this;
    }

    /**
     * @ignore
     */
    public function field($uField)
    {
        $this->field = $uField;

        return $this;
    }

    /**
     * @ignore
     */
    public function errorMessage($uErrorMessage)
    {
        $this->errorMessage = $uErrorMessage;
    }
}
