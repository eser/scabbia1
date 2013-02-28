<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Validation;

use Scabbia\Extensions\String\String;
use Scabbia\Extensions\Validation\ValidationRule;
use Scabbia\Extensions;

/**
 * Validation Extension
 *
 * @package Scabbia
 * @subpackage validation
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends contracts
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
 */
class Validation
{
    /**
     * @ignore
     */
    public static $rules = array();
    /**
     * @ignore
     */
    public static $summary = array();


    /**
     * @ignore
     */
    public static function addRule($uKey = null)
    {
        $tRule = new ValidationRule($uKey);
        self::$rules[] = $tRule;

        return $tRule;
    }

    /**
     * @ignore
     */
    public static function clear()
    {
        self::$rules = array();
        self::$summary = array();
    }

    /**
     * @ignore
     */
    private static function addSummary($uField, $uMessage)
    {
        if (!isset(self::$summary[$uField])) {
            self::$summary[$uField] = array();
        }

        self::$summary[$uField][] = array(
            'field' => $uField,
            'message' => $uMessage
        );
    }

    /**
     * @ignore
     */
    public static function validate(Array $uArray = null)
    {
        if (!is_null($uArray)) {
            foreach (self::$rules as $tRule) {
                if (!array_key_exists($tRule->field, $uArray)) {
                    if ($tRule->type == 'isExist') {
                        self::addSummary($tRule->field, $tRule->errorMessage);
                    }

                    continue;
                }

                $tArgs = $tRule->args;
                array_unshift($tArgs, $uArray[$tRule->field]);

                if (!call_user_func_array('Scabbia\\Extensions\\Contracts\\contracts::' . $tRule->type, $tArgs)->check()) {
                    self::addSummary($tRule->field, $tRule->errorMessage);
                }
            }
        }

        return (count(self::$summary) == 0);
    }

    /**
     * @ignore
     */
    public static function hasErrors()
    {
        $uArgs = func_get_args();

        if (count($uArgs) > 0) {
            return array_key_exists($uArgs[0], self::$summary);
        }

        return (count(self::$summary) > 0);
    }

    /**
     * @ignore
     */
    public static function getErrors($uKey)
    {
        if (!array_key_exists($uKey, self::$summary)) {
            return false;
        }

        return self::$summary[$uKey];
    }

    /**
     * @ignore
     */
    public static function getErrorMessages($uFirsts = false, $uFilter = false)
    {
        $tMessages = array();

        foreach (self::$summary as $tKey => $tField) {
            if ($uFilter !== false && $uFilter != $tKey) {
                continue;
            }

            foreach ($tField as $tSummary) {
                if (is_null($tSummary['message'])) {
                    continue;
                }

                $tMessages[] = $tSummary['message'];
                if ($uFirsts) {
                    break;
                }
            }
        }

        return $tMessages;
    }

    /**
     * @ignore
     */
    public static function getErrorMessagesByFields()
    {
        $tMessages = array();

        foreach (self::$summary as $tField) {
            foreach ($tField as $tRule) {
                if (is_null($tRule->errorMessage)) {
                    continue;
                }

                if (!isset($tMessages[$tField])) {
                    $tMessages[$tField] = array();
                }

                $tMessages[$tField][] = $tRule->errorMessage;
            }
        }

        return $tMessages;
    }

    /**
     * @ignore
     */
    public static function export($tOutput = true)
    {
        return String::vardump(self::$summary, $tOutput);
    }
}
