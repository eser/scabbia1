<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Smtp;

use Scabbia\Config;

/**
 * Smtp Extension
 *
 * @package Scabbia
 * @subpackage Smtp
 * @version 1.1.0
 */
class Smtp
{
    /**
     * @ignore
     */
    public static $host;
    /**
     * @ignore
     */
    public static $port;
    /**
     * @ignore
     */
    public static $username;
    /**
     * @ignore
     */
    public static $password;


    /**
     * @ignore
     *
     * @throws \Exception
     */
    private static function sockwait($uSocket, $uExpectation)
    {
        $tResponse = '';
        while (substr($tResponse, 3, 1) != ' ') {
            if (!($tResponse = fgets($uSocket, 256))) {
                throw new \Exception('read error');
            }
        }

        if (substr($tResponse, 0, 3) != $uExpectation) {
            throw new \Exception('expectation error - expected: ' . $uExpectation . ' response: ' . $tResponse);
        }
    }

    /**
     * @ignore
     */
    public static function send($uFrom, $uTo, $uData)
    {
        $tResult = array();

        self::$host = Config::get('smtp/host', 'localhost');
        self::$port = Config::get('smtp/port', 25);
        self::$username = Config::get('smtp/username');
        self::$password = Config::get('smtp/password');
        // self::$from =;

        $tSmtp = fsockopen(self::$host, self::$port);
        if ($tSmtp !== false) {
            self::sockwait($tSmtp, '220');

            fputs($tSmtp, 'EHLO ' . self::$host . "\n");
            self::sockwait($tSmtp, '250');

            if (strlen(self::$username) > 0) {
                fputs($tSmtp, 'AUTH LOGIN' . "\n");
                self::sockwait($tSmtp, '334');

                fputs($tSmtp, base64_encode(self::$username) . "\n");
                self::sockwait($tSmtp, '334');

                fputs($tSmtp, base64_encode(self::$password) . "\n");
                self::sockwait($tSmtp, '235');
            }

            fputs($tSmtp, 'MAIL FROM: ' . $uFrom . "\n");
            self::sockwait($tSmtp, '250');

            // todo: to+cc+bcc parsing
            fputs($tSmtp, 'RCPT TO: ' . $uTo . "\n");
            self::sockwait($tSmtp, '250');

            fputs($tSmtp, 'DATA' . "\n");
            self::sockwait($tSmtp, '354');

            fputs($tSmtp, $uData . "\n.\n");
            self::sockwait($tSmtp, '250');

            fputs($tSmtp, 'QUIT' . "\n");
            fclose($tSmtp);
        }

        return $tResult;
    }
}
