<?php

	namespace Scabbia;

	/**
	 * Time Extension
	 *
	 * @package Scabbia
	 * @subpackage time
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class time {
		/**
		 * @ignore
		 */
		public static function today() {
			return mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		}

		/**
		 * @ignore
		 */
		public static function now() {
			return time();
		}

		/**
		 * @ignore
		 */
		public static function ago($uDifference) {
			if($uDifference < 60) {
				return array($uDifference, 'seconds');
			}

			$uDifference = round($uDifference / 60);
			if($uDifference < 60) {
				return array($uDifference, 'minutes');
			}

			$uDifference = round($uDifference / 60);
			if($uDifference <= 5) { // 5 hour limit
				return array($uDifference, 'hours');
			}

			return null;
		}

		/**
		 * @ignore
		 */
		public static function humanize($uTimestamp, $uTime = null, $uCalculateAgo = true, $uShowHours = true) {
			if(is_null($uTime)) {
				$uTime = time();
			}

			if(version_compare(PHP_VERSION, '5.3.0', '<')) {
				if($uShowHours) {
					return date('d.m.Y H:i', $uTimestamp);
				}

				return date('d.m.Y', $uTimestamp);
			}

			$tDifference = $uTime - $uTimestamp;

			if($tDifference >= 0 && $uCalculateAgo) {
				$tAgo = self::ago($tDifference);

				if(!is_null($tAgo)) {
					return implode(' ', $tAgo);
				}
			}

			if(date('d.m.Y', $uTime - (24 * 60 * 60)) == date('d.m.Y', $uTimestamp)) {
				if($uShowHours) {
					return 'Yesterday, ' . date('H:i', $uTimestamp);
				}

				return 'Yesterday';
			}

			if(date('d.m.Y', $uTime) == date('d.m.Y', $uTimestamp)) {
				if($uShowHours) {
					return 'Today, ' . date('H:i', $uTimestamp);
				}

				return 'Today';
			}

			if(date('d.m.Y', $uTime + (24 * 60 * 60)) == date('d.m.Y', $uTimestamp)) {
				if($uShowHours) {
					return 'Tomorrow, ' . date('H:i', $uTimestamp);
				}

				return 'Tomorrow';
			}

			if($uShowHours) {
				return date('d.m.Y H:i', $uTimestamp);
			}

			return date('d.m.Y', $uTimestamp);
		}

		/**
		 * @ignore
		 */
		public static function toGmt($uTime = null, $uIsGMT = true) {
			if(!isset($uTime)) {
				$uTime = time();
			}

			return gmdate('D, d M Y H:i:s', $uTime) . ($uIsGMT ? ' GMT' : '');
		}

		/**
		 * @ignore
		 */
		public static function fromGmt($uTime) {
		}

		/**
		 * @ignore
		 */
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

		/**
		 * @ignore
		 */
		public static function fromDos($uTime) {
			$tSeconds = 2 * ($uTime & 0x1f);
			$tMinutes = ($uTime >> 5) & 0x3f;
			$tHours = ($uTime >> 11) & 0x1f;
			$tDays = ($uTime >> 16) & 0x1f;
			$tMonths = ($uTime >> 21) & 0x0f;
			$tYears = ($uTime >> 25) & 0x7f;

			return mktime($tHours, $tMinutes, $tSeconds, $tMonths, $tDays, $tYears + 1980);
		}

		/**
		 * @ignore
		 */
		public static function toDb($uTime, $uFormat = 'd-m-Y H:i:s') {
			if(!is_numeric($uTime)) {
				if(version_compare(PHP_VERSION, '5.3.0', '<')) {
					// Eser: let database to handle that.
					return $uTime;
				}
				else {
					$tTime = date_parse_from_format($uFormat, $uTime);
					$uTime = mktime($tTime['hour'], $tTime['minute'], $tTime['second'], $tTime['month'], $tTime['day'], $tTime['year']); // $tTime['is_dst']
				}
			}

			return date('Y-m-d H:i:s', $uTime);
		}

		/**
		 * @ignore
		 */
		public static function fromDb($uTime) {
			if(version_compare(PHP_VERSION, '5.3.0', '<')) {
				$tTime = sscanf($uTime, '%d-%d-%d %d:%d:%d'); // year, month, day, hour, minute, second
				return mktime($tTime[3], $tTime[4], $tTime[5], $tTime[1], $tTime[2], $tTime[0]);
			}

			$tTime = date_parse_from_format('Y-m-d H:i:s', $uTime);

			return mktime($tTime['hour'], $tTime['minute'], $tTime['second'], $tTime['month'], $tTime['day'], $tTime['year']); // $tTime['is_dst']
		}

		/**
		 * @ignore
		 */
		public static function convert($uTime, $uSourceFormat, $uDestinationFormat = null) {
			if(version_compare(PHP_VERSION, '5.3.0', '<')) {
				$tTime = sscanf($uTime, '%d-%d-%d %d:%d:%d'); // year, month, day, hour, minute, second
				$tTimestamp = mktime($tTime[3], $tTime[4], $tTime[5], $tTime[1], $tTime[2], $tTime[0]);
			}
			else {
				$tTime = date_parse_from_format($uSourceFormat, $uTime);
				$tTimestamp = mktime($tTime['hour'], $tTime['minute'], $tTime['second'], $tTime['month'], $tTime['day'], $tTime['year']); // $tTime['is_dst']
			}

			if(is_null($uDestinationFormat)) {
				return $tTimestamp;
			}

			return date($uDestinationFormat, $tTimestamp);
		}

		/**
		 * @ignore
		 */
		public static function format($uTime, $uFormat) {
			return date($uFormat, $uTime);
		}

		/**
		 * @ignore
		 */
		public static function timezones() {
			return timezone_identifiers_list();
		}
	}

	?>