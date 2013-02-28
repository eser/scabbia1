<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

if (!function_exists('fnmatch')) {
    function fnmatch($uPattern, $uString)
    {
        for ($tBrackets = 0, $tPregPattern = '', $tCount = 0, $tLen = strlen($uPattern); $tCount < $tLen; $tCount++) {
            $tChar = $uPattern[$tCount];

            if (strpbrk($tChar, '\\/-+^$=!.|(){}<>')) {
                $tPregPattern .= '\\' . $tChar;
            } else {
                if (strpbrk($tChar, '?*')) {
                    $tPregPattern .= '.' . $tChar;
                } else {
                    $tPregPattern .= $tChar;
                    if ($tChar == '[') {
                        ++$tBrackets;
                    } else {
                        if ($tChar == ']') {
                            if ($tBrackets == 0) {
                                return false;
                            }

                            $tBrackets--;
                        }
                    }
                }
            }
        }

        if ($tBrackets != 0) {
            return false;
        }

        return preg_match('/' . $tPregPattern . '/i', $uString);
    }
}

// session trans sid
ini_set('session.use_trans_sid', '0');

// replace missing environment variables
/*
            static $sEnvNames = array(
                'HTTP_ACCEPT',
                'HTTP_ACCEPT_LANGUAGE',
                'HTTP_HOST',
                'HTTP_USER_AGENT',
                'HTTP_REFERER',
                'SCRIPT_FILENAME',
                'PHP_SELF',
                'QUERY_STRING',
                'REQUEST_URI',
                'SERVER_ADDR',
                'SERVER_NAME',
                'SERVER_PORT',
                'SERVER_PROTOCOL',
                'HTTPS'
            );

            foreach ($sEnvNames as $tEnv) {
                if (isset($_SERVER[$tEnv])) { // && strlen($_SERVER[$tEnv]) > 0
                    continue;
                }

                $_SERVER[$tEnv] = getenv($tEnv) or $_SERVER[$tEnv] = '';
            }
*/
