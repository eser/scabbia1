<?php

	namespace Scabbia\Extensions\Http;

	use Scabbia\Extensions\Http\request;
	use Scabbia\Extensions\Http\response;
	use Scabbia\Extensions\String\string;
	use Scabbia\Extensions\Views\views;
	use Scabbia\config;
	use Scabbia\events;
	use Scabbia\framework;

	/**
	 * Http Extension
	 *
	 * @package Scabbia
	 * @subpackage http
	 * @version 1.1.0
	 *
	 * @scabbia-fwversion 1.1
	 * @scabbia-fwdepends string
	 * @scabbia-phpversion 5.3.0
	 * @scabbia-phpdepends
	 */
	class http {
		/**
		 * @ignore
		 */
		public static $routes = array();
		/**
		 * @ignore
		 */
		public static $notfoundPage;

		/**
		 * @ignore
		 */
		public static function extensionLoad() {
			// $notfoundPage
			self::$notfoundPage = config::get('/http/errorPages/notfound', '{app}views/shared/error.php');

			// routes
			foreach(config::get('/http/routeList', array()) as $tRouteList) {
				self::routeAdd($tRouteList['match'], $tRouteList['callback']);
			}
		}

		/**
		 * @ignore
		 */
		public static function rewrite(&$uUrl, $uMatch, $uForward, $uLimitMethods = null) {
			if(!is_null($uLimitMethods) && !in_array(request::$methodext, $uLimitMethods)) {
				return false;
			}

			$tReturn = framework::pregReplace($uMatch, $uForward, $uUrl);
			if($tReturn !== false) {
				$uUrl = $tReturn;

				return true;
			}

			return false;
		}

		/**
		 * @ignore
		 */
		public static function routing() {
			$tResolution = self::routeResolve(request::$queryString);

			if(!is_null($tResolution) && call_user_func($tResolution[0], $tResolution[1]) !== false) {
				// to interrupt event-chain execution
				return true;
			}
		}

		/**
		 * @ignore
		 */
		public static function routeResolve($uQueryString) {
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
		public static function routeAdd($uMatch, $uMethod) {
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
		public static function url($uPath) {
			$tParms = array(
				'siteroot' => rtrim(framework::$siteroot, '/'),
				'device' => request::$crawlerType,
				'path' => $uPath
			);

			events::invoke('httpUrl', $tParms);

			return string::format(config::get('/http/link', '{@siteroot}/{@path}'), $tParms);
		}

		/**
		 * @ignore
		 */
		public static function notfound() {
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found', true, 404);

			//! todo internalization.
			// maybe just include?
			views::viewFile(self::$notfoundPage, array(
			                                   'title' => 'Error',
			                                   'message' => '404 Not Found'
			                              ));

			framework::end(1);
		}

		/**
		 * @ignore
		 */
		public static function output($uParms) {
			if(request::$isAjax) {
				$tLastContentType = response::sentHeaderValue('Content-Type');
				$tContent = '{ "isSuccess": ' . (($uParms['exitStatus'][0] > 0) ? 'false' : 'true')
						. ', "errorMessage": ' . (is_null($uParms['exitStatus']) ? 'null' : string::dquote($uParms['exitStatus'][1], true));

				if($tLastContentType == false) {
					response::sendHeader('Content-Type', 'application/json', true);

					$tContent .= ', "object": ' . json_encode($uParms['content']);
				}
				else {
					$tContent .= ', "object": ' . $uParms['content'];
				}

				$tContent .= ' }';

				$uParms['content'] = $tContent;
			}
		}

		/**
		 * @ignore
		 */
		public static function xss($uString) {
			if(is_string($uString)) {
				$tString = str_replace(array('<', '>', '"', '\'', '$', '(', ')', '%28', '%29'), array('&#60;', '&#62;', '&#34;', '&#39;', '&#36;', '&#40;', '&#41;', '&#40;', '&#41;'), $uString); // '&' => '&#38;'
				return $tString;
			}

			return $uString;
		}

		/**
		 * @ignore
		 */
		public static function encode($uString) {
			return urlencode($uString);
		}

		/**
		 * @ignore
		 */
		public static function decode($uString) {
			return urldecode($uString);
		}

		/**
		 * @ignore
		 */
		public static function encodeArray($uArray) {
			$tReturn = array();

			foreach($uArray as $tKey => $tValue) {
				$tReturn[] = urlencode($tKey) . '=' . urlencode($tValue);
			}

			return implode('&', $tReturn);
		}

		/**
		 * @ignore
		 */
		public static function copyStream($tFilename) {
			$tInput = fopen('php://input', 'rb');
			$tOutput = fopen($tFilename, 'wb');
			stream_copy_to_stream($tInput, $tOutput);
			fclose($tOutput);
			fclose($tInput);
		}

		/**
		 * @ignore
		 */
		public static function baseUrl() {
			return '//' . $_SERVER['HTTP_HOST'] . framework::$siteroot;
		}

		/**
		 * @ignore
		 */
		public static function parseHeaderString($uString, $uLowerAll = false) {
			$tResult = array();

			foreach(explode(',', $uString) as $tPiece) {
				// pull out the language, place languages into array of full and primary
				// string structure:
				$tPiece = trim($tPiece);
				if($uLowerAll) {
					$tResult[] = strtolower(substr($tPiece, 0, strcspn($tPiece, ';')));
				}
				else {
					$tResult[] = substr($tPiece, 0, strcspn($tPiece, ';'));
				}
			}

			return $tResult;
		}

	}

	?>