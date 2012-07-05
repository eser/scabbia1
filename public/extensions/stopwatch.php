<?php

if(extensions::isSelected('stopwatch')) {
	/**
	* StopWatch Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class stopwatch {
		public static $markers = array();

		public static function extension_info() {
			return array(
				'name' => 'stopwatch',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		public static function start($uName) {
			self::$markers[$uName] = microtime(true);
		}

		public static function stop($uName) {
			$tValue = self::$markers[$uName];
			unset(self::$markers[$uName]);

			return microtime(true) - $tValue;
		}

		public static function get($uName) {
			return self::$markers[$uName];
		}

		public static function set($uName, $uTime) {
			self::$markers[$uName] = $uTime;
		}
	}
}

?>
