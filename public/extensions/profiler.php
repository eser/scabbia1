<?php

if(extensions::isSelected('profiler')) {
	/**
	* Profiler Extension
	*
	* @package Scabbia
	* @subpackage UtilityExtensions
	*/
	class profiler {
		/**
		* @ignore
		*/
		public static $markers = array();

		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'profiler',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		/**
		* @ignore
		*/
		public static function startTimer($uName) {
			self::$markers[$uName] = microtime(true);
		}

		/**
		* @ignore
		*/
		public static function stopTimer($uName) {
			$tValue = self::$markers[$uName];
			unset(self::$markers[$uName]);

			return microtime(true) - $tValue;
		}

		/**
		* @ignore
		*/
		public static function getTimer($uName) {
			return self::$markers[$uName];
		}

		/**
		* @ignore
		*/
		public static function setTimer($uName, $uTime) {
			self::$markers[$uName] = $uTime;
		}
	}
}

?>