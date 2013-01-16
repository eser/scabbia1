<?php

	namespace Scabbia;

	/**
	 * Router Extension
	 *
	 * @package Scabbia
	 * @subpackage router
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends http
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class router {
		/**
		 * @ignore
		 */
		public static $callbacks = array();

		/**
		 * @ignore
		 */
		public static function run($uParms) {
			$tAutoRun = intval(config::get('/http/autorun', '1'));
			if(!$tAutoRun) {
				return;
			}

			if(extensions::isLoaded('profiler')) {
				profiler::start('http', array('action' => 'routing'));
			}

			$tParms = array(
				'queryString' => &http::$queryString,
				'get' => &$_GET
			);

			foreach(self::$callbacks as $tCallback) {
				if(!is_null($tCallback[2]) && !in_array(http::$methodext, $tCallback[2])) {
					continue;
				}

				$tMatches = framework::pregMatch(ltrim($tCallback[0], '/'), http::$queryString);
				if(count($tMatches) > 0) {
					$tCallbackToCall = $tCallback[1];
					break;
				}
			}

			if(isset($tCallbackToCall)) {
				array_shift($tMatches);
				call_user_func_array($tCallbackToCall, $tMatches);
			}
			else {
				events::invoke('httpRoute', $tParms);
			}

			if(extensions::isLoaded('profiler')) {
				profiler::stop();
			}
		}

		/**
		 * @ignore
		 */
		public static function addCallback($uMatch, $uCallback) {
			if(!is_array($uMatch)) {
				$uMatch = array($uMatch);
			}

			foreach($uMatch as $tMatch) {
				$tParts = explode(' ', $tMatch, 2);

				$tLimitMethods = ((count($tParts) > 1) ? explode(',', strtolower(array_shift($tParts))) : null);

				self::$callbacks[] = array($tParts[0], $uCallback, $tLimitMethods);
			}
		}
	}

	?>