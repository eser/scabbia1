<?php

	/**
	 * SMTP Extension
	 *
	 * @package Scabbia
	 * @subpackage smtp
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class smtp {
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
		 */
		private static function sockwait($uSocket, $uExpectation) {
			$tResponse = '';
			while(substr($tResponse, 3, 1) != ' ') {
				if(!($tResponse = fgets($uSocket, 256))) {
					throw new Exception('read error');
				}
			}

			if(substr($tResponse, 0, 3) != $uExpectation) {
				throw new Exception('expectation error - expected: ' . $uExpectation . ' response: ' . $tResponse);
			}
		}
 
		/**
		 * @ignore
		 */
		public static function &send($uFrom, $uTo, $uSubject, $uContent) {
			$tResult = array();

			self::$host = config::get('/smtp/host', 'localhost');
			self::$port = config::get('/smtp/port', '25');
			self::$username = config::get('/smtp/username');
			self::$password = config::get('/smtp/password');
			// self::$from = config::get('/smtp/from');

			$tSmtp = fsockopen(self::$host, intval(self::$port));
			if($tSmtp !== false) {
				self::sockwait($tSmtp, '220');

				fputs($tSmtp, 'EHLO ' . self::$host . "\r\n");
				self::sockwait($tSmtp, '250');

				fputs($tSmtp, 'AUTH LOGIN' . "\r\n");
				self::sockwait($tSmtp, '334');
				
				fputs($tSmtp, base64_encode(self::$username) . "\r\n");
				self::sockwait($tSmtp, '334');
				
				fputs($tSmtp, base64_encode(self::$password) . "\r\n");
				self::sockwait($tSmtp, '235');
				
				fputs($tSmtp, 'MAIL FROM: ' . $uFrom . "\r\n");
				self::sockwait($tSmtp, '250');

				fputs($tSmtp, 'RCPT TO: ' . $uTo . "\r\n");
				self::sockwait($tSmtp, '250');
				
				fputs($tSmtp, 'DATA' . "\r\n");
				self::sockwait($tSmtp, '354');

				fputs($tSmtp,
					'MIME-Version: 1.0' . "\r\n" .
					'Content-Type: text/html; charset=utf-8' . "\r\n" .
					'From: ' . $uFrom . "\r\n" .
					'To: ' . $uTo . "\r\n" .
					'Subject: ' . $uSubject . "\r\n\r\n" .
					$uContent . "\r\n.\r\n"
				);
				self::sockwait($tSmtp, '250');

				fputs($tSmtp, 'QUIT' . "\r\n");
				fclose($tSmtp);
			}

			return $tResult;
		}
	}

?>