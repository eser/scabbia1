<?php

	/**
	 * MVC Extension
	 *
	 * @package Scabbia
	 * @subpackage mvc
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string, http, resources, models, views
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
		public static $route = null;
		/**
		 * @ignore
		 */
		public static $controllerActual = null;
		/**
		 * @ignore
		 */
		public static $actionActual = null;
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
		public static $models = array();

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
			self::$route = self::findRoute($uParms['get']);
			self::$controllerActual = self::$route['controller'];
			self::$actionActual = self::$route['action'];

			$tParameterSegments = null;
			$tParms = array(
						   'controller' => &self::$route['controller'],
						   'action' => &self::$route['action'],
						   'controllerActual' => &self::$controllerActual,
						   'actionActual' => &self::$actionActual,
						   'parameterSegments' => &$tParameterSegments
					  );
			events::invoke('routing', $tParms);

			if(extensions::isLoaded('profiler')) {
				profiler::start('mvc', array('action' => 'rendering'));
			}
			
			while(true) {
				if(strpos(self::$actionActual, '_') !== false) {
					mvc::notfound();
					break;
				}

				//! todo ensure autoload behaviour.
				if(!is_subclass_of(self::$controllerActual, 'controller')) {
					mvc::notfound();
					break;
				}
				
				$tController = new self::$controllerActual ();
				$tController->view = self::$route['controller'] . '/' . self::$route['action'] . '.' . config::get('/mvc/view/defaultViewExtension', 'php');
				
				try {
					$tReturn = $tController->render(self::$actionActual, self::$route['parametersArray']);
					if($tReturn === false) {
						mvc::notfound();
						break;
					}

					if($tReturn !== true && !is_null($tReturn)) {
						call_user_func($tReturn);
						break;
					}
				}
				catch(Exception $ex) {
					mvc::error($ex->getMessage());
				}

				break;
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
		public static function currentUrl() {
			$tControllerData = self::getControllerData(self::$route['controller']);

			return string::format($tControllerData['link'], array(
				'siteroot' => framework::$siteroot,
				'device' => http::$crawlerType,
				'controller' => self::$route['controller'],
				'action' => self::$route['action'],
				'parameters' => self::$route['parameters'],
				'queryString' => self::$route['queryString']
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
		public static function loaddatabase($uDatabaseName) {
			if(!extensions::isLoaded('database')) {
				return false;
			}

			if(!isset(mvc::$models[$uDatabaseName])) {
				mvc::$models[$uDatabaseName] = database::get($uDatabaseName);
			}

			return mvc::$models[$uDatabaseName];
		}

		/**
		 * @ignore
		 */
		public static function load($uModelClass, $uDatabase = null) {
			if(!isset(mvc::$models[$uModelClass])) {
				mvc::$models[$uModelClass] = new $uModelClass ($uDatabase);
			}

			return mvc::$models[$uModelClass];
		}

		/**
		 * @ignore
		 */
		public static function json($uModel = null) {
			if(is_null($uModel)) {
				$uModel = &views::$vars;
			}

			header('Content-Type: application/json', true);

			echo json_encode(
				$uModel
			);
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

			return call_user_func_array('mvc::urlInternal', $tArgs);
		}

		/**
		 * @ignore
		 */
		public static function redirect() {
			$tArgs = func_get_args();
			$tQuery = call_user_func_array('mvc::urlInternal', $tArgs);

			http::sendRedirect($tQuery, true);
		}

		/**
		 * @ignore
		 */
		public static function export($uAjaxOnly = false) {
			$tArray = array();

			foreach(get_declared_classes() as $tClass) {
				if(!is_subclass_of($tClass, 'controller')) { // && $tClass != 'controller'
					continue;
				}

				$tReflectedClass = new ReflectionClass($tClass);
				foreach($tReflectedClass->getMethods(ReflectionMethod::IS_PUBLIC) as $tMethod) {
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