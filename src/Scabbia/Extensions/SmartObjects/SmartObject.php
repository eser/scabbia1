<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\SmartObjects;

/**
 * SmartObjects Extension: SmartObject Class
 *
 * @package Scabbia
 * @subpackage SmartObjects
 * @version 1.1.0
 */
class SmartObject
{
    /**
     * @ignore
     */
    public $obtained = array();
    /**
     * @ignore
     */
    public $data = array();


    /**
     * @ignore
     */
    public function __isset($uKey)
    {
        if (array_key_exists($uKey, $this->obtained)) {
            return true;
        }

        foreach ($this->data as $tValue) {
            if (!is_array($tValue->fields)) {
                // Smart Object fields should be arrays
                continue;
            }

            foreach ($tValue->fields as $tFieldKey => $tFieldValue) {
                if ((is_numeric($tFieldKey) && $tFieldValue == $uKey) || ($tFieldKey == $uKey)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @ignore
     */
    public function __get($uKey)
    {
        if (array_key_exists($uKey, $this->obtained)) {
            return $this->obtained[$uKey];
        }

        foreach ($this->data as $tValue) {
            if (!is_array($tValue->fields)) {
                // Smart Object fields should be arrays
                continue;
            }

            foreach ($tValue->fields as $tFieldKey => $tFieldValue) {
                if ((is_numeric($tFieldKey) && $tFieldValue == $uKey) || ($tFieldKey == $uKey)) {
                    if (!is_object($this->data[$uKey]) || !method_exists($this->data[$uKey], 'runSmartObject')) {
                        continue;
                    }

                    $this->data[$uKey]->runSmartObject($this);

                    return $this->obtained[$uKey];
                }
            }
        }

        return false;
    }

    /**
     * @ignore
     */
    public function __set($uKey, $uValue)
    {
        if (array_key_exists($uKey, $this->obtained)) {
            $this->obtained[$uKey] = $uValue;

            return;
        }

        $this->{$uKey} = $uValue;
    }

    /**
     * @ignore
     */
    public function __unset($uKey)
    {
        if (array_key_exists($uKey, $this->obtained)) {
            unset($this->obtained[$uKey]);

            return;
        }

        unset($this->{$uKey});
    }

    /**
     * @ignore
     */
    public function removeReference($uOffset)
    {
        unset($this->obtained[$uOffset]);
    }

    /**
     * @ignore
     */
    public function register($uOffset, $uValue)
    {
        foreach ((array)$uOffset as $uOffsetKey) {
            $this->obtained[$uOffsetKey] = $uValue;
        }
    }

    /**
     * @ignore
     */
    public function listItems()
    {
        echo '<h2>Items</h2>';

        foreach ($this->data as $tDataKey => $tDataValue) {
            if (!is_array($tDataValue->fields)) {
                continue;
            }

            echo '<br /><b>', $tDataKey, ': </b><br />';
            foreach ($tDataValue->fields as $tFieldKey => $tFieldValue) {
                if (is_numeric($tFieldKey)) {
                    echo '<pre>', $tFieldValue, ' </pre>';
                    continue;
                }

                echo '<pre>', $tFieldKey, ' </pre>';
            }
        }
    }
}
