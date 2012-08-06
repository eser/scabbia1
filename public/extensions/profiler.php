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
		public static $stack = array();

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
		public static function start($uName, $uParameters = null) {
			$tProfileData = new profilerData($uName, $uParameters);

			self::$stack[] = &$tProfileData;
			$tProfileData->start();
		}

		/**
		* @ignore
		*/
		public static function &stop() {
			$tProfileData = array_pop(self::$stack);

			$tProfileData->stop();

			if(!isset(self::$markers[$tProfileData->name])) {
				self::$markers[$tProfileData->name] = array($tProfileData);
			}
			else {
				self::$markers[$tProfileData->name][] = $tProfileData;
			}

			return $tProfileData;
		}

		/**
		* @ignore
		*/
		public static function clear() {
			while(count(self::$stack) > 0) {
				self::stop();
			}
		}

		/**
		* @ignore
		*/
		public static function &get($uName) {
			return self::$markers[$uName];
		}

		/**
		* @ignore
		*/
		public static function export() {
			return string::vardump(self::$markers, true);
		}
	}

	/**
	* Profiler Data Class
	*
	* @package Scabbia
	* @subpackage UtilityExtensions
	*/
	class profilerData {
		/**
		* @ignore
		*/
		public $name;
		public $parameters;
		public $startTime;
		public $startMemory;
		public $consumedTime;
		public $consumedMemory;

		/**
		* @ignore
		*/
		public function __construct($uName, $uParameters = null) {
			$this->name = $uName;
			$this->parameters = $uParameters;
		}

		/**
		* @ignore
		*/
		public function start() {
			$this->startTime = microtime(true);
			$this->startMemory = memory_get_peak_usage();
		}

		/**
		* @ignore
		*/
		public function stop() {
			$this->consumedTime = microtime(true) - $this->startTime;
			$this->consumedMemory = memory_get_peak_usage() - $this->startMemory;
		}
	}
}

?>