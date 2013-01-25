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
		public static $routes = array();
		/**
		 * @ignore
		 */
		public static $errorPage;

		/**
		 * @ignore
		 */
		public static function extensionLoad() {
			self::$errorPage = config::get('/mvc/view/errorPage', 'shared/error.php');

			foreach(config::get('/router/routeList', array()) as $tRouteList) {
				self::add($tRouteList['match'], $tRouteList['callback']);
			}
		}

		/**
		 * @ignore
		 */
		public static function run($uParms) {
			if(extensions::isLoaded('profiler')) {
				profiler::start('http', array('action' => 'routing'));
			}

			$tParms = array(
				'queryString' => &http::$queryString,
				'get' => &$_GET
			);

			$tEventReturn = events::invoke('httpRoute', $tParms);

			if(!is_null($tEventReturn) && $tEventReturn !== false) {
				$tResolved = self::resolve(http::$queryString);
				if(is_null($tResolved) || call_user_func($tResolved[0], $tResolved[1]) === false) {
					self::notfound();
				}
			}

			if(extensions::isLoaded('profiler')) {
				profiler::stop();
			}
		}

		/**
		 * @ignore
		 */
		public static function resolve($uQueryString) {
			foreach(self::$routes as $tRoute) {
				if(!is_null($tRoute[2]) && !in_array(http::$methodext, $tRoute[2])) { //! todo methodex
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
		public static function notfound() {
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);

			//! todo internalization.
			views::view(self::$errorPage, array(
			                                   'title' => 'Error',
			                                   'message' => '404 Not Found'
			                              ));

			framework::end(1);
		}
	}

	?>