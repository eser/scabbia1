<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Validation;

/**
 * Validation Extension: ContractObject Class
 *
 * @package Scabbia
 * @subpackage Validation
 * @version 1.1.0
 */
class ContractObject
{
    /**
     * @ignore
     */
    public $status;
    /**
     * @ignore
     */
    public $newValue;


    /**
     * @ignore
     */
    public function __construct($uStatus, $uNewValue = null)
    {
        $this->status = $uStatus;
        $this->newValue = $uNewValue;
    }

    /**
     * @ignore
     *
     * @throws \Exception
     */
    public function exception($uErrorMessage)
    {
        if ($this->status) {
            return;
        }

        throw new \Exception($uErrorMessage);
    }

    /**
     * @ignore
     */
    public function check()
    {
        return $this->status;
    }

    /**
     * @ignore
     */
    public function get()
    {
        if (!$this->status) {
            return false;
        }

        return $this->newValue;
    }
}
