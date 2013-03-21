<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\String;

/**
 * @ignore
 */
define('FILTER_VALIDATE_BOOLEAN_FIX', 'filterValidateBooleanFix');

if (!defined('ENT_HTML5')) {
    /**
     * @ignore
     */
    define('ENT_HTML5', (16 | 32));
}

/**
 * String Extension
 *
 * @package Scabbia
 * @subpackage String
 * @version 1.1.0
 *
 * @todo pluralize, singularize
 */
class String
{
    /**
     * @ignore
     */
    public static $tab = "\t";


    /**
     * @ignore
     */
    public static function getEncoding()
    {
        return mb_preferred_mime_name(mb_internal_encoding());
    }

    /**
     * @ignore
     */
    public static function coalesce()
    {
        foreach (func_get_args() as $tValue) {
            if (!is_null($tValue)) {
                if (is_array($tValue)) {
                    if (isset($tValue[0][$tValue[1]]) && !is_null($tValue[0][$tValue[1]])) {
                        return $tValue[0][$tValue[1]];
                    }

                    continue;
                }

                return $tValue;
            }
        }

        return null;
    }

    /**
     * @ignore
     */
    public static function prefixLines($uInput, $uPrefix = '- ', $uLineEnding = PHP_EOL)
    {
        $tLines = explode($uLineEnding, $uInput);

        $tOutput = $tLines[0] . $uLineEnding;
        $tCount = 0;
        foreach ($tLines as $tLine) {
            if ($tCount++ == 0) {
                continue;
            }

            $tOutput .= $uPrefix . $tLine . $uLineEnding;
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function filter()
    {
        $uArgs = func_get_args();

        if ($uArgs[1] == FILTER_VALIDATE_BOOLEAN_FIX) {
            if ($uArgs[0] === true || $uArgs[0] == 'true' || $uArgs[0] === 1 || $uArgs[0] == '1') {
                return true;
            }

            return false;
        } else {
            if (is_callable($uArgs[1], true)) {
                $tValue = array_shift($uArgs);
                $tFunction = $uArgs[0];
                $uArgs[0] = $tValue;

                return call_user_func_array($tFunction, $uArgs);
            }
        }

        return call_user_func_array('filter_var', $uArgs);
    }

    /**
     * @ignore
     */
    public static function format($uString)
    {
        $uArgs = func_get_args();
        array_shift($uArgs);

        if (count($uArgs) > 0 && is_array($uArgs[0])) {
            $uArgs = $uArgs[0];
        }

        $tBrackets = array(array(null, ''));
        $tQuoteChar = false;
        $tLastItem = 0;
        $tArrayItem = 1;

        for ($tPos = 0, $tLen = self::length($uString); $tPos < $tLen; $tPos++) {
            $tChar = self::substr($uString, $tPos, 1);

            if ($tChar == '\\') {
                $tBrackets[$tLastItem][$tArrayItem] .= self::substr($uString, ++$tPos, 1);
                continue;
            }

            if ($tQuoteChar === false && $tChar == '{') {
                ++$tLastItem;
                $tBrackets[$tLastItem] = array(null, null);
                $tArrayItem = 1;
                continue;
            }

            if ($tLastItem > 0) {
                if (is_null($tBrackets[$tLastItem][$tArrayItem])) {
                    if ($tChar == '\'' || $tChar == '"') {
                        $tQuoteChar = $tChar;
                        $tBrackets[$tLastItem][$tArrayItem] = '"'; // static text
                        $tChar = self::substr($uString, ++$tPos, 1);
                    } else {
                        if ($tChar == '!') {
                            $tBrackets[$tLastItem][$tArrayItem] = '!'; // dynamic text
                            $tChar = self::substr($uString, ++$tPos, 1);
                        } else {
                            if ($tChar == '@') {
                                $tBrackets[$tLastItem][$tArrayItem] = '@'; // parameter
                                $tChar = self::substr($uString, ++$tPos, 1);
                            } else {
                                $tBrackets[$tLastItem][$tArrayItem] = '@'; // parameter
                            }
                        }
                    }
                }

                if (self::substr($tBrackets[$tLastItem][$tArrayItem], 0, 1) == '"') {
                    if ($tQuoteChar == $tChar) {
                        $tQuoteChar = false;
                        continue;
                    }

                    if ($tQuoteChar !== false) {
                        $tBrackets[$tLastItem][$tArrayItem] .= $tChar;
                        continue;
                    }

                    if ($tChar != ',' && $tChar != '}') {
                        continue;
                    }
                }

                if ($tArrayItem == 1 && $tChar == '|' && is_null($tBrackets[$tLastItem][0])) {
                    $tBrackets[$tLastItem][0] = $tBrackets[$tLastItem][1];
                    $tBrackets[$tLastItem][1] = null;
                    continue;
                }

                if ($tChar == ',') {
                    $tBrackets[$tLastItem][++$tArrayItem] = null;
                    continue;
                }

                if ($tChar == '}') {
                    $tFunc = array_shift($tBrackets[$tLastItem]);
                    foreach ($tBrackets[$tLastItem] as &$tItem) {
                        switch ($tItem[0]) {
                            case '"':
                                $tItem = self::substr($tItem, 1);
                                break;
                            case '@':
                                $tItem = $uArgs[self::substr($tItem, 1)];
                                break;
                            case '!':
                                $tItem = constant(self::substr($tItem, 1));
                                break;
                        }
                    }

                    if (!is_null($tFunc)) {
                        $tString = call_user_func_array(self::substr($tFunc, 1), $tBrackets[$tLastItem]);
                    } else {
                        $tString = implode(', ', $tBrackets[$tLastItem]);
                    }

                    $tArrayItem = count($tBrackets[$tLastItem - 1]) - 1;
                    $tBrackets[$tLastItem - 1][$tArrayItem] .= $tString;
                    unset($tBrackets[$tLastItem]);
                    $tLastItem--;

                    continue;
                }
            }

            $tBrackets[$tLastItem][$tArrayItem] .= $tChar;
        }

        return $tBrackets[0][1];
    }

    /**
     * @ignore
     */
    public static function vardump($uVariable, $tOutput = true)
    {
        $tVariable = $uVariable;
        $tType = gettype($tVariable);
        $tOut = '';
        static $sTabs = '';

        switch ($tType) {
            case 'boolean':
                $tOut .= '<b>boolean</b>(' . (($tVariable) ? 'true' : 'false') . ')' . PHP_EOL;
                break;
            case 'double':
                $tOut .= '<b>' . $tType . '</b>(\'' . number_format($tVariable, 22, '.', '') . '\')' . PHP_EOL;
                break;
            case 'integer':
            case 'string':
                $tOut .= '<b>' . $tType . '</b>(\'' . $tVariable . '\')' . PHP_EOL;
                break;
            case 'array':
            case 'object':
                if ($tType == 'object') {
                    $tType = get_class($tVariable);
                    $tVariable = get_object_vars($tVariable);
                }

                $tCount = count($tVariable);
                $tOut .= '<b>' . $tType . '</b>(' . $tCount . ')';

                if ($tCount > 0) {
                    $tOut .= ' {' . PHP_EOL;

                    $sTabs .= self::$tab;
                    foreach ($tVariable as $tKey => $tVal) {
                        $tOut .= $sTabs . '[' . $tKey . '] = ';
                        $tOut .= self::vardump($tVal, false);
                    }
                    $sTabs = substr($sTabs, 0, -1);

                    $tOut .= $sTabs . '}';
                }

                $tOut .= PHP_EOL;
                break;
            case 'resource':
                $tOut .= '<b>resource</b>(\'' . get_resource_type($tVariable) . '\')' . PHP_EOL;
                break;
            case 'NULL':
                $tOut .= '<b><i>null</i></b>' . PHP_EOL;
                break;
            case 'unknown type':
            default:
                $tOut .= '<b>unknown</b>' . PHP_EOL;
                break;
        }

        if ($tOutput) {
            echo '<pre>' . $tOut . '</pre>';

            return null;
        }

        return $tOut;
    }

    /**
     * @ignore
     */
    public static function hash($uHash)
    {
        return hexdec(hash('crc32', $uHash) . hash('crc32b', $uHash));
    }

    /**
     * @ignore
     */
    public static function generatePassword($uLength)
    {
        srand(microtime(true) * 1000000);

        static $sVowels = array('a', 'e', 'i', 'o', 'u');
        static $sCons = array('b', 'c', 'd', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'u', 'v', 'w', 'tr', 'cr', 'br', 'fr', 'th', 'dr', 'ch', 'ph', 'wr', 'st', 'sp', 'sw', 'pr', 'sl', 'cl');

        $tConsLen = count($sCons) - 1;
        $tVowelsLen = count($sVowels) - 1;
        for ($tOutput = ''; strlen($tOutput) < $uLength;) {
            $tOutput .= $sCons[rand(0, $tConsLen)] . $sVowels[rand(0, $tVowelsLen)];
        }

        // prevent overflow of size
        return substr($tOutput, 0, $uLength);
    }

    /**
     * @ignore
     */
    public static function generateUuid()
    {
        // return md5(uniqid(mt_rand(), true));
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
                mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
                mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * @ignore
     */
    public static function generate($uLength, $uCharset = '0123456789ABCDEF')
    {
        srand(microtime(true) * 1000000);

        $tCharsetLen = self::length($uCharset) - 1;
        for ($tOutput = ''; $uLength > 0; $uLength--) {
            $tOutput .= self::substr($uCharset, rand(0, $tCharsetLen), 1);
        }

        return $tOutput;
    }

    /**
     * @ignore
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
     * @ignore
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
     * @ignore
     */
    public static function strip($uString, $uValids)
    {
        $tOutput = '';

        for ($tCount = 0, $tLen = self::length($uString); $tCount < $tLen; $tCount++) {
            $tChar = self::substr($uString, $tCount, 1);
            if (self::strpos($uValids, $tChar) === false) {
                continue;
            }

            $tOutput .= $tChar;
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function squote($uString, $uCover = false)
    {
        // if (is_null($uString)) {
        //     return 'null';
        // }

        if ($uCover) {
            return '\'' . strtr($uString, array('\\' => '\\\\', '\'' => '\\\'')) . '\'';
        }

        return strtr($uString, array('\\' => '\\\\', '\'' => '\\\''));
    }

    /**
     * @ignore
     */
    public static function dquote($uString, $uCover = false)
    {
        // if (is_null($uString)) {
        //     return 'null';
        // }

        if ($uCover) {
            return '"' . strtr($uString, array('\\' => '\\\\', '"' => '\\"')) . '"';
        }

        return strtr($uString, array('\\' => '\\\\', '"' => '\\"'));
    }

    /**
     * @ignore
     */
    public static function squoteArray($uArray, $uCover = false)
    {
        static $tSquotes = array('\\' => '\\\\', '\'' => '\\\'');

        $tArray = array();
        foreach ((array)$uArray as $tKey => $tValue) {
            if ($uCover) {
                $tArray[$tKey] = '\'' . strtr($tValue, $tSquotes) . '\'';
                continue;
            }

            $tArray[$tKey] = strtr($tValue, $tSquotes);
        }

        return $tArray;
    }

    /**
     * @ignore
     */
    public static function dquoteArray($uArray, $uCover = false)
    {
        static $tDquotes = array('\\' => '\\\\', '"' => '\\"');

        $tArray = array();
        foreach ((array)$uArray as $tKey => $tValue) {
            if ($uCover) {
                $tArray[$tKey] = '\'' . strtr($tValue, $tDquotes) . '\'';
                continue;
            }

            $tArray[$tKey] = strtr($tValue, $tDquotes);
        }

        return $tArray;
    }

    /**
     * @ignore
     */
    public static function replaceBreaks($uString, $uBreaks = '<br />')
    {
        return strtr($uString, array("\r" => '', "\n" => $uBreaks));
    }

    /**
     * @ignore
     */
    public static function cut($uString, $uLength, $uSuffix = '...')
    {
        if (self::length($uString) <= $uLength) {
            return $uString;
        }

        return rtrim(self::substr($uString, 0, $uLength)) . $uSuffix;
    }

    /**
     * @ignore
     */
    public static function encodeHtml($uString)
    {
        return strtr($uString, array('&' => '&amp;', '"' => '&quot;', '<' => '&lt;', '>' => '&gt;'));
    }

    /**
     * @ignore
     */
    public static function decodeHtml($uString)
    {
        return strtr($uString, array('&amp;' => '&', '&quot;' => '"', '&lt;' => '<', '&gt;' => '>'));
    }

    /**
     * @ignore
     */
    public static function toLower($uString)
    {
        return mb_convert_case($uString, MB_CASE_LOWER);
    }

    /**
     * @ignore
     */
    public static function toUpper($uString)
    {
        return mb_convert_case($uString, MB_CASE_UPPER);
    }

    /**
     * @ignore
     */
    public static function capitalize($uString)
    {
        return mb_convert_case($uString, MB_CASE_TITLE);
    }

    /**
     * @ignore
     */
    public static function length($uString)
    {
        // return mb_strlen($uString);
        return strlen(utf8_decode($uString));
    }

    /**
     * @ignore
     */
    public static function substr($uString, $uStart, $uLength = null)
    {
        if (is_null($uLength)) {
            return mb_substr($uString, $uStart);
        }

        return mb_substr($uString, $uStart, $uLength);
    }

    /**
     * @ignore
     */
    public static function strpos($uString, $uNeedle, $uOffset = 0)
    {
        return mb_strpos($uString, $uNeedle, $uOffset);
    }

    /**
     * @ignore
     */
    public static function strstr($uString, $uNeedle, $uBeforeNeedle = false)
    {
        return mb_strstr($uString, $uNeedle, $uBeforeNeedle);
    }

    /**
     * @ignore
     */
    public static function sizeCalc($uSize, $uPrecision = 0)
    {
        static $sSize = ' KMGT';
        for ($tCount = 0; $uSize >= 1024; $uSize /= 1024, $tCount++) {
            ;
        }

        return round($uSize, $uPrecision) . ' ' . $sSize[$tCount] . 'B';
    }

    /**
     * @ignore
     */
    public static function quantityCalc($uSize, $uPrecision = 0)
    {
        static $sSize = ' KMGT';
        for ($tCount = 0; $uSize >= 1024; $uSize /= 1024, $tCount++) {
            ;
        }

        return round($uSize, $uPrecision) . $sSize[$tCount];
    }

    /**
     * @ignore
     */
    public static function htmlEscape($uString)
    {
        return htmlspecialchars($uString, ENT_COMPAT | ENT_HTML5, mb_internal_encoding());
    }

    /**
     * @ignore
     */
    public static function htmlUnescape($uString)
    {
        return htmlspecialchars_decode($uString, ENT_COMPAT | ENT_HTML5);
    }

    /**
     * @ignore
     */
    private static function readsetGquote($uString, &$uPosition)
    {
        $tInSlash = false;
        $tInQuote = false;
        $tOutput = '';

        for ($tLen = self::length($uString); $uPosition <= $tLen; ++$uPosition) {
            $tChar = self::substr($uString, $uPosition, 1);

            if (($tChar == '\\') && !$tInSlash) {
                $tInSlash = true;
                continue;
            }

            if ($tChar == '"') {
                if (!$tInQuote) {
                    $tInQuote = true;
                    continue;
                }

                if (!$tInSlash) {
                    return $tOutput;
                }
            }
            $tOutput .= $tChar;
            $tInSlash = false;
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function readset($uString)
    {
        $tStart = self::strpos($uString, '[');
        $tOutput = array();
        $tBuffer = '';

        if ($tStart === false) {
            return $tOutput;
        }

        for ($tLen = self::length($uString); $tStart <= $tLen; ++$tStart) {
            $tChar = self::substr($uString, $tStart, 1);

            if ($tChar == ']') {
                $tOutput[] = $tBuffer;

                return $tOutput;
            }

            if ($tChar == ',') {
                $tOutput[] = $tBuffer;
                $tBuffer = '';
                continue;
            }

            if ($tChar == '"') {
                $tBuffer = self::readsetGquote($uString, $tStart);
                continue;
            }
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function parseQueryString($uString, $uParameters = '?&', $uKeys = '=', $uSeperator = null)
    {
        $tParts = explode('#', $uString, 2);

        $tParsed = array(
            '_segments' => array(),
            '_hash' => isset($tParts[1]) ? $tParts[1] : null
        );

        $tStrings = array('', '');
        $tStrIndex = 0;

        $tPos = 0;
        $tLen = self::length($tParts[0]);

        if (!is_null($uSeperator)) {
            for (; $tPos < $tLen; $tPos++) {
                $tChar = self::substr($tParts[0], $tPos, 1);

                if (self::strpos($uSeperator, $tChar) !== false) {
                    if (self::length($tStrings[1]) > 0) {
                        $tParsed['_segments'][] = $tStrings[1];
                    }

                    $tStrings = array('', null);
                    continue;
                }

                if (self::strpos($uParameters, $tChar) !== false) {
                    break;
                }

                $tStrings[1] .= $tChar;
            }
        }

        if (self::length($tStrings[1]) > 0) {
            if (self::length($tStrings[1]) > 0) {
                $tParsed['_segments'][] = $tStrings[1];
            }

            $tStrings = array('', null);
        }

        for (; $tPos < $tLen; $tPos++) {
            $tChar = self::substr($tParts[0], $tPos, 1);

            if (self::strpos($uParameters, $tChar) !== false) {
                if (self::length($tStrings[0]) > 0 && !array_key_exists($tStrings[0], $tParsed)) {
                    $tParsed[$tStrings[0]] = $tStrings[1];
                    $tStrIndex = 0;
                }

                $tStrings = array('', null);
                continue;
            }

            if (self::strpos($uKeys, $tChar) !== false && $tStrIndex < 1) {
                ++$tStrIndex;
                $tStrings[$tStrIndex] = '';
                continue;
            }

            $tStrings[$tStrIndex] .= $tChar;
        }

        if (self::length($tStrings[0]) > 0) {
            if (self::length($tStrings[0]) > 0 && !array_key_exists($tStrings[0], $tParsed)) {
                $tParsed[$tStrings[0]] = $tStrings[1];
            }
        }

        return $tParsed;
    }

    /**
     * @ignore
     */
    public static function removeAccent($uString)
    {
        static $tAccented = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'þ', 'Þ', 'ð');
        static $tStraight = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'b', 'B', 'o');

        return str_replace($tAccented, $tStraight, $uString);
    }

    /**
     * @ignore
     */
    public static function removeInvisibles($uString)
    {
        static $tInvisibles = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 11, 12, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 127);
        $tOutput = '';

        for ($tCount = 0, $tLen = self::length($uString); $tCount < $tLen; $tCount++) {
            $tChar = self::substr($uString, $tCount, 1);

            if (in_array(ord($tChar), $tInvisibles)) {
                continue;
            }

            $tOutput .= $tChar;
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function slug($uString)
    {
        $uString = self::removeInvisibles($uString);
        $uString = self::removeAccent($uString);
        $uString = strtolower(trim($uString));
        $uString = preg_replace('/[^a-z0-9-]/', '_', $uString);
        $uString = preg_replace('/-+/', '_', $uString);

        return $uString;
    }

    /**
     * @ignore
     */
    public static function ordinalize($uNumber)
    {
        if (in_array(($uNumber % 100), range(11, 13))) {
            return $uNumber . 'th';
        }

        switch ($uNumber % 10) {
            case 1:
                return $uNumber . 'st';
                break;
            case 2:
                return $uNumber . 'nd';
                break;
            case 3:
                return $uNumber . 'rd';
                break;
            default:
                return $uNumber . 'th';
                break;
        }
    }

    /**
     * @ignore
     */
    public static function capitalizeEx($uString, $uDelimiter = ' ', $uReplaceDelimiter = null)
    {
        $tOutput = '';
        $tCapital = true;

        for ($tPos = 0, $tLen = self::length($uString); $tPos < $tLen; $tPos++) {
            $tChar = self::substr($uString, $tPos, 1);

            if ($tChar == $uDelimiter) {
                $tCapital = true;
                $tOutput .= (!is_null($uReplaceDelimiter)) ? $uReplaceDelimiter : $tChar;
                continue;
            }

            if ($tCapital) {
                $tOutput .= self::toUpper($tChar);
                $tCapital = false;
                continue;
            }

            $tOutput .= $tChar;
        }

        return $tOutput;
    }

    /**
     * @ignore
     */
    public static function swap(&$uVariable1, &$uVariable2)
    {
        $tTemp = $uVariable1;
        $uVariable1 = $uVariable2;
        $uVariable2 = $tTemp;
    }
}
