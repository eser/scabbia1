<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Objects;

use Scabbia\Extensions\Helpers\Arrays;
use Scabbia\Extensions\Http\Request;

/**
 * Objects Extension: FormHandler Class
 *
 * @package Scabbia
 * @subpackage Objects
 * @version 1.1.0
 */
class FormHandler
{
    /**
     * @ignore
     */
    public $records = array();


    /**
     * @ignore
     */
    public static function getFromRequest($uChangeField, array $uFields)
    {
        $tNewInstance = new static();
        $tChangedRecords = Request::post($uChangeField, array());

        $tFieldValues = array();
        foreach ($uFields as $tField) {
            $tFieldValues[$tField] = Request::post($tField);
        }

        foreach ($tChangedRecords as $tIndex => $tChangedRecord) {
            $tRecord = array(
                'index' => $tIndex,
                'changed' => $tChangedRecord
            );
            foreach ($uFields as $tField) {
                $tRecord[$tField] = isset($tFieldValues[$tField][$tIndex]) ? $tFieldValues[$tField][$tIndex] : null;
            }

            $tNewInstance->records[] = $tRecord;
        }

        return $tNewInstance;
    }

    /**
     * @ignore
     */
    public function getInserted()
    {
        return Arrays::getRows($this->records, 'changed', 'insert');
    }

    /**
     * @ignore
     */
    public function getUpdated()
    {
        return Arrays::getRows($this->records, 'changed', 'update');
    }

    /**
     * @ignore
     */
    public function getDeleted()
    {
        return Arrays::getRows($this->records, 'changed', 'delete');
    }

    /**
     * @ignore
     */
    public function getNotModified()
    {
        return Arrays::getRows($this->records, 'changed', 'none');
    }

    /**
     * @ignore
     */
    public function getButDeleted()
    {
        return Arrays::getRowsBut($this->records, 'changed', 'delete');
    }
}
