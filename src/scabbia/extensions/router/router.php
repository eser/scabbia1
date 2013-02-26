<?php

	namespace Scabbia\Extensions\Router;

	use Scabbia\Extensions\Profiler\profiler;
	use Scabbia\config;
	use Scabbia\extensions;

	/**
	 * Router Extension
	 *
	 * @package Scabbia
	 * @subpackage router
	 * @version 1.1.0
	 *
	 * @scabbia-fwversion 1.1
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.3.0
	 * @scabbia-phpdepends
	 */
	class router {
		/**
		 * @ignore
		 */
		public static $rewrites = null;
		/**
		 * @ignore
		 */
		public static $routes = null;


		/**
		 * @ignore
		 */
		private static function load() {
			if(!is_null(self::$rewrites)) {
				return;
			}

			self::$rewrites = array();
			foreach(config::get('/http/rewriteList', array()) as $tRewriteList) {
				self::addRewrite($tRewriteList['match'], $tRewriteList['forward'], !isset($tRewriteList['limitMethods']) ? array_keys($tRewriteList['limitMethods']) : null);
			}

			self::$routes = array();
			foreach(config::get('/http/routeList', array()) as $tRouteList) {
				self::addRoute($tRouteList['match'], $tRouteList['callback']);
			}
		}

		/**
		 * @ignore
		 */
		public static function routing() {
			$tResolution = self::resolve(request::$queryString);

			if(!is_null($tResolution) && call_user_func($tResolution[0], $tResolution[1]) !== false) {
				// to interrupt event-chain execution
				return true;
			}
		}

		/**
		 * @ignore
		 */
		public static function resolve($uQueryString) {
			self::load();

			/*
			if(isset($tRewriteList['limitMethods']) && !is_null($uMethodext) && !in_array($uMethodext, array_keys($tRewriteList['limitMethods']))) {
				continue;
			}

			if(self::rewriteUrl($uUrl, $tRewriteList['match'], $tRewriteList['forward'])) {
				break;
			}
			*/


			foreach(self::$routes as $tRoute) {
				if(!is_null($tRoute[2]) && !in_array(request::$methodext, $tRoute[2])) { //! todo methodex
					continue;
				}

				$tMatches = framework::pregMatch(ltrim($tRoute[0], '/'), $uQueryString);

				if(count($tMatches) > 0) {
					return array($tRoute[1], $tMatches);
				}
			}

			return null;
		}

		/**
		 * @ignore
		 */
		public static function add($uMatch, $uMethod) {
			self::load();

			if(!is_array($uMatch)) {
				$uMatch = array($uMatch);
			}

			foreach($uMatch as $tMatch) {
				$tParts = explode(' ', $tMatch, 2);

				$tLimitMethods = ((count($tParts) > 1) ? explode(',', strtolower(array_shift($tParts))) : null);

				self::$routes[] = array($tParts[0], $uMethod, $tLimitMethods);
			}
		}

		/**
		 * @ignore
		 */
		public static function route($uCallbacks, $uOtherwise = null) {
			profiler::start('router', array('action' => 'routing'));

			foreach((array)$uCallbacks as $tCallback) {
				$tReturn = call_user_func($tCallback);

				if(!is_null($tReturn) && $tReturn === true) {
					break;
				}
			}

			if(!is_null($uOtherwise) && !isset($tReturn) || $tReturn !== true) {
				call_user_func($uOtherwise);
			}

			profiler::stop();
		}
	}

	?>