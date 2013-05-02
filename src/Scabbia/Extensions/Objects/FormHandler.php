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

            if (!isset($tNewInstance->records[$tChangedRecord])) {
                $tNewInstance->records[$tChangedRecord] = array();
            }
            $tNewInstance->records[$tChangedRecord][] = $tRecord;
        }

        return $tNewInstance;
    }

    /**
     * @ignore
     */
    public function getInserted()
    {
        return Arrays::get($this->records, 'insert', array());
    }

    /**
     * @ignore
     */
    public function getUpdated()
    {
        return Arrays::get($this->records, 'update', array());
    }

    /**
     * @ignore
     */
    public function getDeleted()
    {
        return Arrays::get($this->records, 'delete', array());
    }
}
