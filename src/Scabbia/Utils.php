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
     * Returns a php file source to view.
     *
     * @param string    $uInput         string path of source file
     * @param bool      $uOnlyContent   returns just file content without comments
     *
     * @return array|string the file content in printable format with or without comments
     */
    public static function printFile($uInput, $uOnlyContent = true)
    {
        $tDocComments = array();
        $tReturn = '';
        $tLastToken = -1;
        $tOpenStack = array();

        foreach (token_get_all($uInput) as $tToken) {
            if (is_array($tToken)) {
                $tTokenId = $tToken[0];
                $tTokenContent = $tToken[1];
            } else {
                $tTokenId = null;
                $tTokenContent = $tToken;
            }

            // $tReturn .= PHP_EOL . token_name($tTokenId) . PHP_EOL;
            switch ($tTokenId) {
                case T_OPEN_TAG:
                    $tReturn .= '<' . '?php ';
                    array_push($tOpenStack, $tTokenId);
                    break;
                case T_OPEN_TAG_WITH_ECHO:
                    $tReturn .= '<' . '?php echo ';
                    array_push($tOpenStack, $tTokenId);
                    break;
                case T_CLOSE_TAG:
                    $tLastOpen = array_pop($tOpenStack);

                    if ($tLastOpen == T_OPEN_TAG_WITH_ECHO) {
                        $tReturn .= '; ';
                    } else {
                        if ($tLastToken != T_WHITESPACE) {
                            $tReturn .= ' ';
                        }
                    }

                    $tReturn .= '?' . '>';
                    break;
                case T_COMMENT:
                case T_DOC_COMMENT:
                    if (substr($tTokenContent, 0, 3) == '/**') {
                        $tCommentContent = substr($tTokenContent, 2, strlen($tTokenContent) - 4);

                        foreach (explode("\n", $tCommentContent) as $tLine) {
                            $tLineContent = ltrim($tLine, "\t ");

                            if (substr($tLineContent, 0, 3) == '* @') {
                                $tLineContents = explode(' ', substr($tLineContent, 3), 2);
                                if (count($tLineContents) < 2) {
                                    continue;
                                }

                                if (!isset($tDocComments[$tLineContents[0]])) {
                                    $tDocComments[$tLineContents[0]] = array();
                                }

                                $tDocComments[$tLineContents[0]][] = $tLineContents[1];
                            }
                        }
                    }
                    break;
                case T_WHITESPACE:
                    if ($tLastToken != T_WHITESPACE &&
                        $tLastToken != T_OPEN_TAG &&
                        $tLastToken != T_OPEN_TAG_WITH_ECHO &&
                        $tLastToken != T_COMMENT &&
                        $tLastToken != T_DOC_COMMENT
                    ) {
                        $tReturn .= ' ';
                    }
                    break;
                case null:
                    $tReturn .= $tTokenContent;
                    if ($tLastToken == T_END_HEREDOC) {
                        $tReturn .= "\n";
                        $tTokenId = T_WHITESPACE;
                    }
                    break;
                default:
                    $tReturn .= $tTokenContent;
                    break;
            }

            $tLastToken = $tTokenId;
        }

        while (count($tOpenStack) > 0) {
            $tLastOpen = array_pop($tOpenStack);
            if ($tLastOpen == T_OPEN_TAG_WITH_ECHO) {
                $tReturn .= '; ';
            } else {
                if ($tLastToken != T_WHITESPACE) {
                    $tReturn .= ' ';
                }
            }

            $tReturn .= '?' . '>';
        }

        if (!$uOnlyContent) {
            $tArray = array(&$tReturn, $tDocComments);

            return $tArray;
        }

        return $tReturn;
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
     * Adds a new framework-variable into array.
     *
     * @param string $uKey   name of variable
     * @param string $uValue value of variable
     */
    public static function addVariable($uKey, $uValue)
    {
        self::$variables['{' . $uKey . '}'] = $uValue;
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
        return strtr(
            $uInput,
            self::$variables
        );
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
}
