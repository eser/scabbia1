<?php

if(extensions::isSelected('time')) {
	/**
	* Time Extension
	*
	* @package Scabbia
	* @subpackage UtilityExtensions
	*/
	class time {
		public static function extension_info() {
			return array(
				'name' => 'time',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		public static function today() {
			return mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		}

		public static function now() {
			return time();
		}

		public static function toGmt($uTime = null, $uIsGMT = true) {
			if(!isset($uTime)) {
				$uTime = time();
			}

			return gmdate('D, d M Y H:i:s', $uTime) . ($uIsGMT ? ' GMT' : '');
		}

		public static function fromGmt($uTime) {
		}

		public static function toDos($uTime = null) {
			if(!isset($uTime)) {
				$uTime = time();
			}

			$tTimeArray = getdate($uTime);

			if($tTimeArray['year'] < 1980) {
				$tTimeArray['year'] = 1980;
				$tTimeArray['mon'] = 1;
				$tTimeArray['mday'] = 1;
				$tTimeArray['hours'] = 0;
				$tTimeArray['minutes'] = 0;
				$tTimeArray['seconds'] = 0;
			}

			// 4byte: hi=date, lo=time
			return (($tTimeArray['year'] - 1980) << 25) | ($tTimeArray['mon'] << 21) | ($tTimeArray['mday'] << 16) | ($tTimeArray['hours'] << 11) | ($tTimeArray['minutes'] << 5) | ($tTimeArray['seconds'] >> 1);
		}

		public static function fromDos($uTime) {
			$tSeconds = 2 * ($uTime & 0x1f);
			$tMinutes = ($uTime >>  5) & 0x3f;
			$tHours = ($uTime >> 11) & 0x1f;
			$tDays = ($uTime >> 16) & 0x1f;
			$tMonths = ($uTime >> 21) & 0x0f;
			$tYears = ($uTime >> 25) & 0x7f;

			return mktime($tHours, $tMinutes, $tSeconds, $tMonths, $tDays, $tYears + 1980);
		}

		public static function toMysql($uTime = null) {
			if(!isset($uTime)) {
				$uTime = time();
			}

			return date('Y-m-d H:i:s', $uTime);
		}

		public static function fromMysql($uTime) {
			$tTime = sscanf($uTime, '%d-%d-%d %d:%d:%d'); // year, month, day, hour, minute, second

			return mktime($tTime[3], $tTime[4], $tTime[5], $tTime[1], $tTime[2], $tTime[0]);
		}

		public static function timezones() {
		}
	}
}

?>
