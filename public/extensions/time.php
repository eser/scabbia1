<?php

	class time {
		public static function extension_info() {
			return array(
				'name' => 'time',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'fwversion' => '1.0',
				'enabled' => true,
				'autoevents' => false,
				'depends' => array()
			);
		}

		public static function gmdate($uFormat = null, $uTime = null, $uIsGMT = false) {
			if(!isset($uFormat)) {
				$uFormat = 'D, d M Y H:i:s';
			}

			if(!isset($uTime)) {
				$uTime = time();
			}

			return gmdate($uFormat, $uTime) . ($uIsGMT ? ' GMT' : '');
		}

		public static function dostime($uTime = null) {
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

		public static function fromMysqlTime($uDate) {
			$tDate = sscanf($uDate, '%d-%d-%d %d:%d:%d'); // year, month, day, hour, minute, second

			return mktime($tDate[3], $tDate[4], $tDate[5], $tDate[1], $tDate[2], $tDate[0]);
		}
	}

?>
