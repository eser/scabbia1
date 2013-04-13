<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Validation;

/**
 * Validation Extension: Conditions Class
 *
 * @package Scabbia
 * @subpackage Validation
 * @version 1.1.0
 *
 * @todo add more conditions such as phone, hex, octal, digit, isUnique, etc.
 * @todo use multibyte functions or String extension
 */
class Conditions
{
    /**
     * @ignore
     */
    public static $externalConditions = array();


    /**
     * @ignore
     */
    public static function __callStatic($uName, array $uArgs)
    {
        if (!isset(self::$externalConditions[$uName])) {
            throw new \Exception('invalid condition - ' . $uName);
        }

        return call_user_func_array(self::$externalConditions[$uName], $uArgs);
    }

    /**
     * @ignore
     */
    public static function register($uKey, Callable $uCallback)
    {
        self::$externalConditions[$uKey] = $uCallback;
    }

    /**
     * @ignore
     */
    public static function isRequired($uValue)
    {
        if (strlen(chop($uValue)) == 0) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isBoolean($uValue)
    {
        if ($uValue !== false && $uValue !== true &&
                $uValue != 'false' && $uValue != 'true' &&
                $uValue !== 0 && $uValue !== 1 &&
                $uValue != '0' && $uValue != '1'
        ) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isFloat($uValue)
    {
        if (filter_var($uValue, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND) === false) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isInteger($uValue)
    {
        if (filter_var($uValue, FILTER_VALIDATE_INT) === false) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isHex($uValue)
    {
        if (filter_var($uValue, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_HEX) === false) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isOctal($uValue)
    {
        if (filter_var($uValue, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_OCTAL) === false) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isNumeric($uValue)
    {
        if (ctype_digit($uValue) === false) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isSlugString($uValue)
    {
        for ($i = mb_strlen($uValue) - 1; $i >= 0; $i--) {
            $tChar = mb_substr($uValue, $i, 1);

            if (!ctype_alnum($tChar) && $tChar != '-') {
                return false;
            }
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isDate($uValue, $uFormat)
    {
        $tArray = date_parse_from_format($uFormat, $uValue);
        if ($tArray['error_count'] > 0) {
            return false;
        }

        if (!checkdate($tArray['month'], $tArray['day'], $tArray['year'])) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isUuid($uValue)
    {
        if (strlen($uValue) != 36) {
            return false;
        }

        for ($i = strlen($uValue) - 1; $i >= 0; $i--) {
            if ($i == 8 || $i == 13 || $i == 18 || $i == 23) {
                if ($uValue[$i] != '-') {
                    return false;
                }

                continue;
            }

            if (!ctype_xdigit($uValue[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isEqual()
    {
        $uArgs = func_get_args();
        $uValue = array_shift($uArgs);

        for ($tCount = count($uArgs) - 1; $tCount >= 0; $tCount--) {
            if ($uValue == $uArgs[$tCount]) {
                $tPasses = true;
                break;
            }
        }

        if (!isset($tPasses)) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isMinimum($uValue, $uOtherValue)
    {
        if ($uValue < $uOtherValue) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isLower($uValue, $uOtherValue)
    {
        if ($uValue >= $uOtherValue) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isMaximum($uValue, $uOtherValue)
    {
        if ($uValue > $uOtherValue) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isGreater($uValue, $uOtherValue)
    {
        if ($uValue <= $uOtherValue) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function length($uValue, $uOtherValue)
    {
        if (strlen($uValue) != $uOtherValue) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function lengthMinimum($uValue, $uOtherValue)
    {
        if (strlen($uValue) < $uOtherValue) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function lengthMaximum($uValue, $uOtherValue)
    {
        if (strlen($uValue) > $uOtherValue) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function inArray($uValue, array $uArray)
    {
        if (!in_array($uValue, $uArray)) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function inKeys($uKey, array $uArray)
    {
        if (!array_key_exists($uKey, $uArray)) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function inString($uValue, $uString)
    {
        if (strpos($uString, $uValue) === false) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function regExp($uValue, $uExpression)
    {
        if (!preg_match($uExpression, $uValue)) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function custom($uValue, Callable $uCallback)
    {
        if (!call_user_func($uCallback, $uValue)) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isNotFalse($uValue)
    {
        if ($uValue === false) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isEmail($uValue)
    {
        if (filter_var($uValue, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isUrl($uValue)
    {
        if (filter_var($uValue, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function isIpAddress($uValue)
    {
        if (filter_var($uValue, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        return true;
    }

    /**
     * @ignore
     */
    public static function getEmail($uValue)
    {
        // if (filter_var($uValue, FILTER_VALIDATE_EMAIL) === false) {
        //    return false;
        // }

        $uValue = strtr($uValue, 'ABCDEFGHIJKLMNOPRQSTUVWXYZ', 'abcdefghijklmnoprqstuvwxyz');

        $tValidated = array('', '');
        $tIndex = 1;
        for ($i = strlen($uValue) - 1; $i >= 0; $i--) {
            if ($uValue[$i] == '@') {
                if (--$tIndex <= 0) {
                    continue;
                }

                // direct termination
                return false;
            }

            if (strpos('abcdefghijklmnoprqstuvwxyz0123456789.+-_', $uValue[$i]) !== false) {
                $tValidated[$tIndex] = $uValue[$i] . $tValidated[$tIndex];
            }
        }

        if ($tIndex > 0) {
            return false;
        }

        return $tValidated[0] . '@' . $tValidated[1];
    }
}
