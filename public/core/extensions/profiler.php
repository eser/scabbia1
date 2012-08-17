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
			if(framework::phpVersion('5.3.6')) {
				$tBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			}
			else {
				$tBacktrace = debug_backtrace(false);
			}

			$tLast = current($tBacktrace);
			$uSource = array('file' => $tLast['file'], 'line' => $tLast['line']);

			$tProfileData = new profilerData($uName, $uParameters, $uSource);

			self::$stack[] = &$tProfileData;
			$tProfileData->start();
		}

		/**
		* @ignore
		*/
		public static function &stop($uExtraParameters = null) {
			$tProfileData = array_pop(self::$stack);

			if(is_null($tProfileData)) {
				return $tProfileData;
			}

			$tProfileData->stop();

			if(!is_null($uExtraParameters)) {
				$tProfileData->addParameters($uExtraParameters);
			}

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
		public static function export($tOutput = true) {
			return string::vardump(self::$markers, $tOutput);
		}

		/**
		* @ignore
		*/
		public static function exportStack($tOutput = true) {
			return string::vardump(self::$stack, $tOutput);
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
		public $source;
		public $startTime;
		public $startMemory;
		public $consumedTime;
		public $consumedMemory;

		/**
		* @ignore
		*/
		public function __construct($uName, $uParameters = null, $uSource) {
			$this->name = $uName;
			$this->parameters = $uParameters;
			$this->source = $uSource;
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

		/**
		* @ignore
		*/
		public function addParameters($uNewParameters) {
			$this->parameters += $uNewParameters;
		}
	}
}

?>