<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Framework;

/**
 * Global utility functions which helps framework execution.
 *
 * @package Scabbia
 * @version 1.1.0
 */
class Utils
{
    /**
     * @var array Presets which can be used in regular expression
     */
    public static $regexpPresets = array(
        'num' => '[0-9]+',
        'num?' => '[0-9]*',
        'alnum' => '[a-zA-Z0-9]+',
        'alnum?' => '[a-zA-Z0-9]*',
        'any' => '[a-zA-Z0-9\.\-_%=]+', // ~
        'any?' => '[a-zA-Z0-9\.\-_%=]*',
        'all' => '.+',
        'all?' => '.*'
    );
    /**
     * @var array Array of framework variables
     */
    public static $variables = array(

    );


    /**
     * Checks the given framework version is greater than running one.
     *
     * @param string    $uVersion   framework version
     *
     * @return bool running framework version is greater than parameter
     */
    public static function version($uVersion)
    {
        return version_compare(Framework::VERSION, $uVersion, '>=');
    }

    /**
     * Checks the given php version is greater than running one.
     *
     * @param string    $uVersion   php version
     *
     * @example ../../examples/scabbia/utils/phpVersion.php How to use this function
     * @return bool running php version is greater than parameter
     */
    public static function phpVersion($uVersion)
    {
        return version_compare(PHP_VERSION, $uVersion, '>=');
    }

    /**
     * Converts presets to the regular expressions.
     *
     * @param string    $uPattern   the pattern to search for, as a string
     *
     * @return string generated regular expression
     */
    private static function pregFormat($uPattern)
    {
        $tBuffer = array(array(false, ''));
        $tBrackets = 0;

        for ($tPos = 0, $tLen = strlen($uPattern); $tPos < $tLen; $tPos++) {
            $tChar = substr($uPattern, $tPos, 1);

            if ($tChar == '\\') {
                $tBuffer[$tBrackets][1] .= substr($uPattern, ++$tPos, 1);
                continue;
            }

            if ($tChar == '(') {
                $tBuffer[++$tBrackets] = array(false, '');
                continue;
            }

            if ($tBrackets > 0) {
                if ($tChar == ':' && $tBuffer[$tBrackets][0] === false) {
                    $tBuffer[$tBrackets][0] = $tBuffer[$tBrackets][1];
                    $tBuffer[$tBrackets][1] = '';

                    continue;
                }

                if ($tChar == ')') {
                    --$tBrackets;
                    $tLast = array_pop($tBuffer);

                    if ($tLast[0] === false) {
                        $tBuffer[$tBrackets][1] .= '(?:';
                    } else {
                        $tBuffer[$tBrackets][1] .= '(?P<' . $tLast[0] . '>';
                    }

                    if (isset(self::$regexpPresets[$tLast[1]])) {
                        $tBuffer[$tBrackets][1] .= self::$regexpPresets[$tLast[1]] . ')';
                    } else {
                        $tBuffer[$tBrackets][1] .= $tLast[1] . ')';
                    }

                    continue;
                }
            }

            if ($tChar == ')') {
                $tBuffer[$tBrackets][1] .= '\\)';
                continue;
            }

            $tBuffer[$tBrackets][1] .= $tChar;
        }

        while ($tBrackets > 0) {
            --$tBrackets;
            $tLast = array_pop($tBuffer);
            $tBuffer[0][1] .= '\\(' . $tLast[1];
        }

        return $tBuffer[0][1];
    }

    /**
     * Searches subject for a match to the regular expression given in pattern.
     *
     * @param string    $uPattern   the pattern to search for, as a string
     * @param string    $uSubject   the input string
     * @param string    $uModifiers the PCRE modifiers
     *
     * @return array the matches
     */
    public static function pregMatch($uPattern, $uSubject, $uModifiers = '^')
    {
        $tPattern = self::pregFormat($uPattern);

        if (strpos($uModifiers, '^') === 0) {
            preg_match('#^' . $tPattern . '$#' . substr($uModifiers, 1), $uSubject, $tResult);
        } else {
            preg_match('#' . $tPattern . '#' . $uModifiers, $uSubject, $tResult);
        }

        // if (count($tResult) > 0) {
        //    return $tResult;
        // }
        //
        // return false;

        return $tResult;
    }

    /**
     * Replaces subject with the matches of the regular expression given in pattern.
     *
     * @param string    $uPattern       the pattern to search for, as a string
     * @param string    $uReplacement   the replacement string
     * @param string    $uSubject       the string or an array with strings to replace
     * @param string    $uModifiers     the PCRE modifiers
     *
     * @return array the result of replace operation
     */
    public static function pregReplace($uPattern, $uReplacement, $uSubject, $uModifiers = '^')
    {
        $tPattern = self::pregFormat($uPattern);

        if (strpos($uModifiers, '^') === 0) {
            $tResult = preg_replace(
                '#^' . $tPattern . '$#' . substr($uModifiers, 1),
                $uReplacement,
                $uSubject,
                -1,
                $tCount
            );
        } else {
            $tResult = preg_replace(
                '#' . $tPattern . '#' . $uModifiers,
                $uReplacement,
                $uSubject,
                -1,
                $tCount
            );
        }

        if ($tCount > 0) {
            return $tResult;
        }

        return false;
    }

    /**
     * Replaces placeholders in given string with framework-variables.
     *
     * @param string $uInput the string with placeholders
     *
     * @return string translated string
     */
    public static function translate($uInput)
    {
        foreach (self::$variables as $tKey => $tValue) {
            if (!is_scalar($tValue)) {
                continue;
            }

            $uInput = str_replace('{' . $tKey . '}', $tValue, $uInput);
        }

        return $uInput;
    }

    /**
     * Encrypts the plaintext with the given key.
     *
     * @param string    $uString    the plaintext
     * @param string    $uKey       the key
     *
     * @return string   ciphertext
     */
    public static function encrypt($uString, $uKey)
    {
        $tResult = '';

        for ($i = 1, $tCount = strlen($uString); $i <= $tCount; $i++) {
            $tChar = substr($uString, $i - 1, 1);
            $tKeyChar = substr($uKey, ($i % strlen($uKey)) - 1, 1);
            $tResult .= chr(ord($tChar) + ord($tKeyChar));
        }

        return $tResult;
    }

    /**
     * Decrypts the ciphertext with the given key.
     *
     * @param string    $uString    the ciphertext
     * @param string    $uKey       the key
     *
     * @return string   plaintext
     */
    public static function decrypt($uString, $uKey)
    {
        $tResult = '';

        for ($i = 1, $tCount = strlen($uString); $i <= $tCount; $i++) {
            $tChar = substr($uString, $i - 1, 1);
            $tKeyChar = substr($uKey, ($i % strlen($uKey)) - 1, 1);
            $tResult .= chr(ord($tChar) - ord($tKeyChar));
        }

        return $tResult;
    }

    /**
     * adds new objects into given array
     *
     * @param array $uArray
     * @param mixed $uIterativeObject
     *
     * @return array merged array
     */
    public static function addToArray(array &$uArray, $uIterativeObject)
    {
        foreach ($uIterativeObject as $tObject) {
            $uArray[] = $tObject;
        }

        return $uArray;
    }

    /**
     * Get Subclasses
     *
     * @param string $uClassName name of the parent class
     * @param bool   $uJustKeys  return keys without reflection instances
     *
     * @return array list of sub classes
     */
    public static function getSubclasses($uClassName, $uJustKeys = false)
    {
        $tClasses = array();

        foreach (get_declared_classes() as $tClass) {
            if (!is_subclass_of($tClass, $uClassName)) {
                continue;
            }

            $tReflection = new \ReflectionClass($tClass);
            if ($tReflection->isAbstract()) {
                continue;
            }

            if ($uJustKeys) {
                $tClasses[] = $tClass;
                continue;
            }

            $tClasses[$tClass] = $tReflection;
        }

        return $tClasses;
    }
}
