<?php

	namespace Scabbia\Extensions\Profiler;

	use Scabbia\Extensions\Profiler\profilerData;
	use Scabbia\Extensions\String\string;
	use Scabbia\extensions;

	/**
	 * Profiler Extension
	 *
	 * @package Scabbia
	 * @subpackage profiler
	 * @version 1.1.0
	 *
	 * @scabbia-fwversion 1.1
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.3.0
	 * @scabbia-phpdepends
	 */
	class profiler {
		/**
		 * @ignore
		 */
		public static $markers = array();
		/**
		 * @ignore
		 */
		public static $stack = array();

		/**
		 * @ignore
		 */
		public static function start($uName, $uParameters = null) {
			/*
			if(framework::phpVersion('5.3.6')) {
				$tBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			}
			else {
				$tBacktrace = debug_backtrace(false);
			}
			*/
			$tBacktrace = debug_backtrace();

			$tLast = current($tBacktrace);
			$uSource = array('file' => $tLast['file'], 'line' => $tLast['line']);

			$tProfileData = new profilerData($uName, $uParameters, $uSource);

			self::$stack[] = $tProfileData;
			$tProfileData->start();
		}

		/**
		 * @ignore
		 */
		public static function stop($uExtraParameters = null) {
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
		public static function get($uName) {
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

	?>