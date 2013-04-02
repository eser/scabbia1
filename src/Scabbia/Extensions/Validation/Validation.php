<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
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
 * @subpackage Validation
 * @version 1.1.0
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
    public static function validate(array $uArray = null)
    {
        if (!is_null($uArray)) {
            foreach (self::$rules as $tRule) {
                if (!isset($uArray[$tRule->field])) {
                    continue;
                }

                $tResult = null;
                foreach ($tRule->conditions as $tCondition) {
                    $tArgs = $tCondition[1];
                    array_unshift($tArgs, $uArray[$tRule->field]);

                    $tSingleResult = call_user_func_array(
                        'Scabbia\\Extensions\\Validation\\Conditions::' . $tCondition[0],
                        $tArgs
                    );

                    if (is_null($tResult) || is_null($tCondition[2])) {
                        $tResult = $tSingleResult;
                    } elseif ($tCondition[2] == 'and') {
                        $tResult = $tResult && $tSingleResult;
                    } elseif ($tCondition[2] == 'or') {
                        $tResult = $tResult || $tSingleResult;
                    }
                }

                if ($tResult === false) {
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
            return isset(self::$summary[$uArgs[0]]);
        }

        return (count(self::$summary) > 0);
    }

    /**
     * @ignore
     */
    public static function getErrors($uKey)
    {
        if (!isset(self::$summary[$uKey])) {
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
