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
	 * @scabbia-fwdepends string, http, router, models, views, controllers
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
		public static $controllerList = null;
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
		public static $link;

		/**
		 * @ignore
		 */
		public static function extensionLoad() {
			self::$defaultController = config::get('/mvc/defaultController', 'home');
			self::$defaultAction = config::get('/mvc/defaultAction', 'index');
			self::$link = config::get('/mvc/link', '{@siteroot}/{@controller}/{@action}{@params}{@query}');
		}

		/*
		 * @ignore
		 */
		private static function getControllers() {
			if(is_null(self::$controllerList)) {
				self::$controllerList = array();

				foreach(get_declared_classes() as $tClass) {
					if(!is_subclass_of($tClass, 'Scabbia\\controller')) {
						continue;
					}

					$tPos = strpos($tClass, '\\');
					if($tPos !== false) {
						self::$controllerList[substr($tClass, $tPos + 1)] = $tClass;
						continue;
					}

					self::$controllerList[$tClass] = $tClass;
				}
			}
		}

		/**
		 * @ignore
		 */
		public static function route($uParams) {
			if(!isset($uParams['controller']) || strlen($uParams['controller']) <= 0) {
				$uParams['controller'] = self::$defaultController;
			}

			if(!isset($uParams['action']) || strlen($uParams['action']) <= 0) {
				$uParams['action'] = self::$defaultAction;
			}

			$tActualController = $uParams['controller'];
			$tActualAction = $uParams['action'];

			if(isset($uParams['params']) && strlen($uParams['params']) > 0) {
				$tSegments = explode('/', ltrim($uParams['params'], '/'));
			}
			else {
				$tSegments = array();
			}

			$tParms = array(
				'controller' => &$uParams['controller'],
				'action' => &$uParams['action'],
				'controllerActual' => &$tActualController,
				'actionActual' => &$tActualAction,
				'parameterSegments' => &$tSegments
			);

			self::getControllers();

			while(true) {
				if(strpos($tActualAction, '_') !== false) {
					$tReturn = null;
					break;
				}

				//! todo ensure autoload behaviour.
				if(!isset(self::$controllerList[$tActualController])) {
					$tReturn = null;
					break;
				}

				$tController = new self::$controllerList[$tActualController] ();
				$tController->route = $uParams;
				$tController->view = $uParams['controller'] . '/' . $uParams['action'] . '.' . config::get('/mvc/view/defaultViewExtension', 'php');

				array_push(self::$controllerStack, $tController);

				try {
					$tReturn = $tController->render($tActualAction, $tSegments);
					if($tReturn === false) {
						array_pop(self::$controllerStack);
						break;
					}

					// call callback/closure returned by render
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
		public static function httpUrl(&$uParms) {
			$tResolved = router::resolve($uParms['path']);

			if(is_null($tResolved)) {
				return;
			}

			$uParms['controller'] = isset($tResolved[1]['controller']) ? $tResolved[1]['controller'] : '';
			$uParms['action'] = isset($tResolved[1]['action']) ? $tResolved[1]['action'] : '';
			$uParms['params'] = isset($tResolved[1]['params']) ? $tResolved[1]['params'] : '';
			$uParms['query'] = isset($tResolved[1]['query']) ? $tResolved[1]['query'] : '';
		}

		/**
		 * @ignore
		 */
		public static function generate($uPath) {
			self::getControllers();

			$tResolved = router::resolve($uPath);
			if(is_null($tResolved)) {
				return false;
			}

			$tRoute = $tResolved[1];
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
				if(!isset(self::$controllerList[$tActualController])) {
					$tReturn = false;
					break;
				}

				$tController = new self::$controllerList[$tActualController] ();
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
		public static function current() {
			return end(self::$controllerStack);
		}

		/**
		 * @ignore
		 */
		public static function currentUrl() {
			$tCurrent = self::current();

			return string::format(self::$link, array(
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
		private static function urlInternal($uPath) {
			$tParms = array(
				'siteroot' => framework::$siteroot,
				'device' => http::$crawlerType,
				'path' => $uPath
			);

			events::invoke('httpUrl', $tParms);

			return string::format(self::$link, $tParms);
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