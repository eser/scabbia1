<?php

	namespace Scabbia;

	/**
	 * MVC Extension
	 *
	 * @package Scabbia
	 * @subpackage mvc
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string, http, models, views, controllers
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 *
	 * @todo remove underscore '_' in controller, action names
	 * @todo forbid 'shared' for controller names
	 * @todo controller and action names localizations
	 * @todo selective loading with controller imports
	 * @todo routing optimizations.
	 * @todo map controller to path (/docs/index/* => views/docs/*.md)
	 */
	class mvc {
		/**
		 * @ignore
		 */
		public static $controllerStack = array();
		/**
		 * @ignore
		 */
		public static $defaultController;
		/**
		 * @ignore
		 */
		public static $defaultAction;
		/**
		 * @ignore
		 */
		public static $errorPage;

		/**
		 * @ignore
		 */
		public static function extensionLoad() {
			self::$defaultController = config::get('/mvc/routes/defaultController', 'home');
			self::$defaultAction = config::get('/mvc/routes/defaultAction', 'index');
			self::$errorPage = config::get('/mvc/view/errorPage', 'shared/error.php');
		}

		/**
		 * @ignore
		 */
		public static function httpRoute(&$uParms) {
			if(extensions::isLoaded('profiler')) {
				profiler::start('mvc', array('action' => 'rendering'));
			}

			$tReturn = self::generate($uParms['get']);
			if($tReturn === false) {
				mvc::notfound();
			}

			if(extensions::isLoaded('profiler')) {
				profiler::stop();
			}

			// to interrupt event-chain execution
			return false;
		}

		/**
		 * @ignore
		 */
		public static function httpUrl(&$uParms) {
			$tSegments = self::findRoute($uParms['path']);

			$uParms['controller'] = $tSegments['controller'];
			$uParms['action'] = $tSegments['action'];
			$uParms['parameters'] = $tSegments['parameters'];
			$uParms['queryString'] = $tSegments['queryString'];
		}

		/**
		 * @ignore
		 */
		public static function generate($uPath) {
			$tRoute = self::findRoute($uPath);
			$tActualController = $tRoute['controller'];
			$tActualAction = $tRoute['action'];

			$tParameterSegments = null;
			$tParms = array(
				'controller' => &$tRoute['controller'],
				'action' => &$tRoute['action'],
				'controllerActual' => &$tActualController,
				'actionActual' => &$tActualAction,
				'parameterSegments' => &$tParameterSegments
			);
			events::invoke('routing', $tParms);

			while(true) {
				if(strpos($tActualAction, '_') !== false) {
					$tReturn = false;
					break;
				}

				//! todo ensure autoload behaviour.
				if(!is_subclass_of($tActualController, 'Scabbia\\controller')) {
					$tReturn = false;
					break;
				}

				$tController = new $tActualController ();
				$tController->route = $tRoute;
				$tController->view = $tRoute['controller'] . '/' . $tRoute['action'] . '.' . config::get('/mvc/view/defaultViewExtension', 'php');

				array_push(self::$controllerStack, $tController);

				try {
					$tReturn = $tController->render($tActualAction, $tRoute['parametersArray']);
					if($tReturn === false) {
						array_pop(self::$controllerStack);
						break;
					}

					if($tReturn !== true && !is_null($tReturn)) {
						call_user_func($tReturn);
						array_pop(self::$controllerStack);
						break;
					}

					array_pop(self::$controllerStack);
				}
				catch(\Exception $ex) {
					mvc::error($ex->getMessage());

					array_pop(self::$controllerStack);
					$tReturn = false;
				}

				break;
			}

			return $tReturn;
		}

		/**
		 * @ignore
		 */
		protected static function getControllerData($uController) {
			$tControllerData = array(
				'actionUrlKeys' => config::get('/mvc/routes/actionUrlKeys', '1'),
				'defaultAction' => self::$defaultAction,
				'link' => config::get('/mvc/routes/link', '{@siteroot}/{@controller}/{@action}{@parameters}{@queryString}')
			);

			foreach(config::get('/mvc/controllerList', array()) as $tController) {
				if($uController != $tController['name']) {
					continue;
				}

				if(isset($tController['actionUrlKeys'])) {
					$tControllerData['actionUrlKeys'] = $tController['actionUrlKeys'];
				}

				if(isset($tController['defaultAction'])) {
					$tControllerData['defaultAction'] = $tController['defaultAction'];
				}

				if(isset($tController['link'])) {
					$tControllerData['link'] = $tController['link'];
				}

				break;
			}

			return $tControllerData;
		}


		/**
		 * @ignore
		 */
		public static function current() {
			return end(self::$controllerStack);
		}

		/**
		 * @ignore
		 */
		public static function currentUrl() {
			$tCurrent = self::current();
			$tControllerData = self::getControllerData($tCurrent->route['controller']);

			return string::format($tControllerData['link'], array(
			                                                     'siteroot' => framework::$siteroot,
			                                                     'device' => http::$crawlerType,
			                                                     'controller' => $tCurrent->route['controller'],
			                                                     'action' => $tCurrent->route['action'],
			                                                     'parameters' => $tCurrent->route['parameters'],
			                                                     'queryString' => $tCurrent->route['queryString']
			                                                ));
		}

		/**
		 * @ignore
		 */
		public static function findRoute($uArgs) {
			if(!is_array($uArgs)) {
				$uArgs = http::parseGet($uArgs);
			}

			$tControllerUrlKey = config::get('/mvc/routes/controllerUrlKey', '0');

			$tRoute = array();

			if(array_key_exists($tControllerUrlKey, $uArgs['_segments']) && strlen($uArgs['_segments'][$tControllerUrlKey]) > 0) {
				$tRoute['controller'] = $uArgs['_segments'][$tControllerUrlKey];
				unset($uArgs['_segments'][$tControllerUrlKey]);
			}
			else {
				$tRoute['controller'] = self::$defaultController;
			}

			$tControllerData = self::getControllerData($tRoute['controller']);

			$tActionKeys = explode(',', $tControllerData['actionUrlKeys']);
			$tRoute['action'] = '';

			foreach($tActionKeys as $tActionKey) {
				if(!isset($uArgs['_segments'][$tActionKey])) {
					break;
				}

				if(strlen($tRoute['action']) > 0) {
					$tRoute['action'] .= '/';
				}

				$tRoute['action'] .= $uArgs['_segments'][$tActionKey];
				unset($uArgs['_segments'][$tActionKey]);
			}

			if(strlen($tRoute['action']) == 0) {
				$tRoute['action'] = $tControllerData['defaultAction'];
			}

			$tRoute['parameters'] = '';
			$tRoute['parametersArray'] = array();
			foreach($uArgs['_segments'] as $tSegment) {
				$tRoute['parameters'] .= '/' . $tSegment;
				$tRoute['parametersArray'][] = $tSegment;
			}

			unset($uArgs['_segments']);
			unset($uArgs['_hash']);

			$tRoute['queryString'] = http::buildQueryString($uArgs);
			$tRoute['queryStringArray'] = $uArgs;

			return $tRoute;
		}

		/**
		 * @ignore
		 */
		public static function error($uMessage) {
			// header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
			header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK', true, 200);

			//! todo internalization.
			if(!http::$isAjax) {
				views::view(self::$errorPage, array(
				                                   'title' => 'Error',
				                                   'message' => $uMessage
				                              ));
			}

			framework::end(1, $uMessage);
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

		/**
		 * @ignore
		 */
		private static function urlInternal($uPath) {
			$tParms = array(
				'siteroot' => framework::$siteroot,
				'device' => http::$crawlerType,
				'path' => $uPath
			);

			events::invoke('httpUrl', $tParms);

			$tControllerData = self::getControllerData($tParms['controller']);

			return string::format($tControllerData['link'], $tParms);
		}

		/**
		 * @ignore
		 */
		public static function url() {
			$tArgs = func_get_args();

			return call_user_func_array('Scabbia\\mvc::urlInternal', $tArgs);
		}

		/**
		 * @ignore
		 */
		public static function redirect() {
			$tArgs = func_get_args();
			$tQuery = call_user_func_array('Scabbia\\mvc::urlInternal', $tArgs);

			http::sendRedirect($tQuery, true);
		}

		/**
		 * @ignore
		 */
		public static function export($uAjaxOnly = false) {
			$tArray = array();

			foreach(get_declared_classes() as $tClass) {
				if(!is_subclass_of($tClass, 'Scabbia\\controller')) { // && $tClass != 'controller'
					continue;
				}

				$tReflectedClass = new \ReflectionClass($tClass);
				foreach($tReflectedClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $tMethod) {
					if($tMethod->class == 'controller') {
						continue;
					}

					$tPos = strpos($tMethod->name, 'ajax_');
					if($uAjaxOnly && $tPos === false) {
						continue;
					}

					if(!isset($tArray[$tMethod->class])) {
						$tArray[$tMethod->class] = array();
					}

					$tArray[$tMethod->class][] = $tMethod->name;
				}
			}

			return $tArray;
		}

		/**
		 * @ignore
		 */
		public static function exportAjaxJs() {
			$tArray = self::export(true);

			$tReturn = <<<EOD
	\$l.ready(function() {
		\$l.extend({
EOD;
			foreach($tArray as $tClassName => $tClass) {
				$tLines = array();

				if(isset($tFirst)) {
					$tReturn .= ',';
				}
				else {
					$tFirst = false;
				}

				$tReturn .= PHP_EOL . "\t\t\t" . $tClassName . ': {' . PHP_EOL;

				foreach($tClass as $tMethod) {
					$tMethods = explode('_', $tMethod, 2);
					if(count($tMethods) < 2 || strpos($tMethods[0], 'ajax') === false) {
						continue;
					}

					$tLines[] = "\t\t\t\t" . $tMethods[1] . ': function(values, fnc) { $l.ajax.post(\'' . self::url($tClassName . '/' . strtr($tMethods[1], '_', '/')) . '\', values, fnc); }';
				}
				$tReturn .= implode(',' . PHP_EOL, $tLines) . PHP_EOL . "\t\t\t" . '}';
			}
			$tReturn .= <<<EOD

		});
	});
EOD;

			return $tReturn;
		}
	}

	?>