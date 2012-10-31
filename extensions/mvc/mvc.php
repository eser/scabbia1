<?php

	/**
	* MVC Extension
	*
	* @package Scabbia
	* @subpackage mvc
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends string, http
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
		public static $controllerStack = array();
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
		public static $viewEngines = array();

		/**
		* @ignore
		*/
		public static function extension_load() {
			self::$defaultController = config::get('/mvc/routes/defaultController', 'home');
			self::$defaultAction = config::get('/mvc/routes/defaultAction', 'index');
			self::$errorPage = config::get('/mvc/view/errorPage', 'shared/error.php');

			foreach(config::get('/mvc/view/viewEngineList', array()) as $tViewEngine) {
				self::registerViewEngine($tViewEngine['extension'], $tViewEngine['class']);
			}

			self::registerViewEngine('php', 'viewengine_php');
		}

		/**
		* @ignore
		*/
		public static function http_route($uParms) {
			self::$route = self::findRoute($_GET);
			self::$controllerActual = self::$route['controller'];
			self::$actionActual = self::$route['action'];

			$tParameterSegments = null;
			events::invoke('routing', array(
				'controller' => &self::$route['controller'],
				'action' => &self::$route['action'],
				'controllerActual' => &self::$controllerActual,
				'actionActual' => &self::$actionActual,
				'parameterSegments' => &$tParameterSegments
			));

			if(extensions::isLoaded('profiler')) {
				profiler::start('mvc', array('action' => 'rendering'));
			}

			while(true) {
				if(strpos(self::$actionActual, '_') !== false) {
					mvc::notfound();
					break;
				}

				$tActionMethodName = strtr(self::$actionActual, '/', '_');
				
				if(http::$isAjax && method_exists(self::$controllerActual, http::$method . 'Ajax_' . $tActionMethodName)) {
					$tActionMethodName = http::$method . 'Ajax_' . $tActionMethodName;
				}
				else if(method_exists(self::$controllerActual, http::$method . '_' . $tActionMethodName)) {
					$tActionMethodName = http::$method . '_' . $tActionMethodName;
				}
				else if(!method_exists(self::$controllerActual, $tActionMethodName)) {
					mvc::notfound();
					break;
				}

				$tController = new self::$controllerActual ();
				self::$controllerStack[] = &$tController;
				$tController->view = self::$route['controller'] . '/' . self::$route['action'] . '.' . config::get('/mvc/view/defaultViewExtension', 'php');

				try {
					if($tController->render($tActionMethodName, self::$route['parametersArray']) === false) {
						mvc::notfound();
						break;
					}
				}
				catch(Exception $ex) {
					mvc::error($ex->getMessage());
				}

				array_pop(self::$controllerStack);
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
		public static function registerViewEngine($uExtension, $uClassName) {
			if(isset(self::$viewEngines[$uExtension])) {
				return;
			}

			self::$viewEngines[$uExtension] = $uClassName;
		}

		/**
		* @ignore
		*/
		protected static function &getControllerData($uController) {
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
			foreach($uArgs['_segments'] as &$tSegment) {
				$tRoute['parameters'] .= '/' . $tSegment;
				$tRoute['parametersArray'][] = $tSegment;
			}

			unset($uArgs['_segments']);
			unset($uArgs['_hash']);

			$tRoute['queryString'] = http::buildQueryString($uArgs);
			$tRoute['queryStringArray'] = &$uArgs;

			return $tRoute;
		}

		/**
		* @ignore
		*/
		public static function view() {
			$uArgs = func_get_args();

			if(count(self::$controllerStack) > 0) {
				$uController = end(self::$controllerStack);
				$uView = (isset($uArgs[0])) ? $uArgs[0] : $uController->view;
				$uModel = (isset($uArgs[1])) ? $uArgs[1] : $uController->vars;
			}
			else {
				$uController = 'shared';
				$uView = (isset($uArgs[0])) ? $uArgs[0] : null;
				$uModel = (isset($uArgs[1])) ? $uArgs[1] : null;
			}

			if(is_null($uView)) {
				throw new Exception('no view file specified.');
			}

			$tViewFilePath = framework::$applicationPath . 'views/' . $uView;
			$tViewExtension = pathinfo($tViewFilePath, PATHINFO_EXTENSION);
			if(!isset(self::$viewEngines[$tViewExtension])) {
				$tViewExtension = config::get('/mvc/view/defaultViewExtension', 'php');
			}

			$tExtra = array(
				'controller' => &$uController,
				'root' => framework::$siteroot
			);

			if(extensions::isLoaded('i8n')) {
				$tExtra['lang'] = i8n::$language['key'];
			}

			$tTemplatePath = pathinfo($tViewFilePath, PATHINFO_DIRNAME) . '/';
			$tViewFile = pathinfo($tViewFilePath, PATHINFO_BASENAME);

			$tViewArray = array(
				'templatePath' => &$tTemplatePath,
				'templateFile' => &$tViewFile,
				'compiledPath' => framework::writablePath('cache/' . $tViewExtension . '/'),
				'compiledFile' => crc32($tViewFilePath),
				'model' => &$uModel,
				'extra' => &$tExtra
			);

			call_user_func(
				self::$viewEngines[$tViewExtension] . '::renderview',
				$tViewArray
			);
		}

		/**
		* @ignore
		*/
		public static function viewFile($uView) {
			$uArgs = func_get_args();

			if(count(self::$controllerStack) > 0) {
				$uController = end(self::$controllerStack);
				$uModel = (isset($uArgs[1])) ? $uArgs[1] : $uController->vars;
			}
			else {
				$uController = 'shared';
				$uModel = (isset($uArgs[1])) ? $uArgs[1] : null;
			}

			$tViewFilePath = framework::translatePath($uView);
			$tViewExtension = pathinfo($tViewFilePath, PATHINFO_EXTENSION);
			if(!isset(self::$viewEngines[$tViewExtension])) {
				$tViewExtension = config::get('/mvc/view/defaultViewExtension', 'php');
			}

			$tExtra = array(
				'controller' => &$uController,
				'root' => framework::$siteroot
			);

			if(extensions::isLoaded('i8n')) {
				$tExtra['lang'] = i8n::$language['key'];
			}

			$tTemplatePath = pathinfo($tViewFilePath, PATHINFO_DIRNAME) . '/';
			$tViewFile = pathinfo($tViewFilePath, PATHINFO_BASENAME);

			$tViewArray = array(
				'templatePath' => &$tTemplatePath,
				'templateFile' => &$tViewFile,
				'compiledPath' => framework::writablePath('cache/' . $tViewExtension . '/'),
				'compiledFile' => crc32($uView),
				'model' => &$uModel,
				'extra' => &$tExtra
			);

			call_user_func(
				self::$viewEngines[$tViewExtension] . '::renderview',
				$tViewArray
			);
		}

		/**
		* @ignore
		*/
		public static function json() {
			$uArgs = func_get_args();

			$uController = end(self::$controllerStack);
			if(count($uArgs) >= 1) {
				$uModel = &$uArgs[0];
			}
			else {
				$uModel = &$uController->vars;
			}

			http::sendHeader('Content-Type', 'application/json', true);

			echo json_encode(
				$uModel
			);
		}

		/**
		* @ignore
		*/
		public static function error($uMessage) {
			if(!http::$isAjax) {
				self::view(self::$errorPage, array(
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
			self::view(self::$errorPage, array(
				'title' => 'Error',
				'message' => '404 Not Found'
			));

			framework::end(1);
		}

		/**
		* @ignore
		*/
		private static function url_internal($uArgs) {
			$tSegments = self::findRoute(
				string::format(
					$uArgs,
					self::$route
				)
			);

			$tArray = array(
				'siteroot' => framework::$siteroot,
				'device' => http::$crawlerType,
				'controller' => $tSegments['controller'],
				'action' => $tSegments['action'],
				'parameters' => $tSegments['parameters'],
				'queryString' => $tSegments['queryString']
			);

			if(extensions::isLoaded('i8n')) {
				$tArray['language'] = i8n::$language['key'];
			}

			$tControllerData = self::getControllerData($tArray['controller']);
			return string::format($tControllerData['link'], $tArray);
/*
			if(count($uArgs) == 1) {
				if(!is_array($uArgs[0])) {
					return $uArgs[0];
				}

				$tQueryParameters = array();
				$tQueryParameters['siteroot'] = string::coalesce(array($uArgs[0], 'siteroot'), framework::$siteroot);
				$tQueryParameters['controller'] = string::coalesce(array($uArgs[0], 'controller'), self::$route['controller'], self::$defaultController);
				$tQueryParameters['action'] = string::coalesce(array($uArgs[0], 'action'), self::$defaultAction);
				$tQueryParameters['device'] = string::coalesce(array($uArgs[0], 'device'), http::$crawlerType);
				$tQueryParameters['language'] = string::coalesce(array($uArgs[0], 'language'), i8n::$language['key']);
				$tQueryParameters['queryString'] = string::coalesce(array($uArgs[0], 'queryString'), '');
			}
			else {
				$tQueryParameters = array();
				$tQueryParameters['siteroot'] = framework::$siteroot;
				$tQueryParameters['controller'] = $uArgs[0];

				$tControllerData = self::getControllerData($tQueryParameters['controller']);

				$tQueryParameters['action'] = string::coalesce(array($uArgs, 1), self::$defaultAction);
				$tQueryParameters['device'] = string::coalesce(array($uArgs, 2), http::$crawlerType);
				$tQueryParameters['language'] = string::coalesce(array($uArgs, 3), i8n::$language['key']);
				$tQueryParameters['queryString'] = string::coalesce(array($uArgs, 4), '');
			}

			return string::format($tControllerData['link'], $tQueryParameters);
*/
		}

		/**
		* @ignore
		*/
		public static function url() {
			$tArgs = func_get_args();
			return call_user_func_array('mvc::url_internal', $tArgs);
		}

		/**
		* @ignore
		*/
		public static function redirect() {
			$tArgs = func_get_args();
			$tQuery = call_user_func_array('mvc::url_internal', $tArgs);

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

					$tPos = strpos($tMethod->name, 'Ajax_');
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
		public static function &exportAjaxJs() {
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
				$tMethod = substr($tMethod, 0, -5);
				$tLines[] = "\t\t\t\t" . $tMethod . ': function(values, fnc) { $l.ajax.post(\'' . self::url($tClassName . '/' . strtr($tMethod, '_', '/')) . '\', values, fnc); }';
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

	/**
	* Model Class
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*/
	abstract class model {
		/**
		* @ignore
		*/
		public $controller;
		/**
		* @ignore
		*/
		public $db;

		/**
		* @ignore
		*/
		public function __construct($uController = null) {
			$this->controller = &$uController;
			if(extensions::isLoaded('database')) {
				$this->db = database::get(); // default database to member 'db'
			}
		}

		/**
		* @ignore
		*/
		public function loaddatabase($uDatabaseName, $uMemberName = null) {
			if(!extensions::isLoaded('database')) {
				return false;
			}

			if(is_null($uMemberName)) {
				$uMemberName = $uDatabaseName;
			}

			$this->{$uMemberName} = database::get($uDatabaseName);
			return true;
		}
	}

	/**
	* Controller Class
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*/
	abstract class controller {
		/**
		* @ignore
		*/
		public $view = null;
		/**
		* @ignore
		*/
		public $db;
		/**
		* @ignore
		*/
		public $vars = array();

		/**
		* @ignore
		*/
		public function __construct() {
			if(extensions::isLoaded('database')) {
				$this->db = database::get(); // default database to member 'db'
			}
		}

		/**
		* @ignore
		*/
		public function loaddatabase($uDatabaseName, $uMemberName = null) {
			if(!extensions::isLoaded('database')) {
				return false;
			}

			if(is_null($uMemberName)) {
				$uMemberName = $uDatabaseName;
			}

			$this->{$uMemberName} = database::get($uDatabaseName);
			return true;
		}

		/**
		* @ignore
		*/
		public function load($uModelClass, $uMemberName = null) {
			if(is_null($uMemberName)) {
				$uMemberName = $uModelClass;
			}

			// if(isset($this->{$uMemberName})) {
			//	return;
			// }

			$this->{$uMemberName} = new $uModelClass ($this);
		}

		/**
		* @ignore
		*/
		public function mapDirectory($uDirectory, $uExtension, $uAction, $uArgs) {
			$tMap = io::mapFlatten(framework::translatePath($uDirectory), '*' . $uExtension, true, true);

			array_unshift($uArgs, $uAction);
			$tPath = implode('/', $uArgs);

			if(in_array($tPath, $tMap, true)) {
				$this->view($uDirectory . $tPath . $uExtension);
				return true;
			}

			return false;
		}

		/**
		* @ignore
		*/
		public function render(&$uAction, &$uArgs) {
			$tCallback = array(&$this, $uAction);

			if(is_callable($tCallback)) {
				return call_user_func_array($tCallback, $uArgs);
			}

			return false;
		}

		/**
		* @ignore
		*/
		public function get($uKey) {
			return $this->vars[$uKey];
		}

		/**
		* @ignore
		*/
		public function set($uKey, $uValue) {
			$this->vars[$uKey] = $uValue;
		}

		/**
		* @ignore
		*/
		public function setRef($uKey, &$uValue) {
			$this->vars[$uKey] = &$uValue;
		}

		/**
		* @ignore
		*/
		public function remove($uKey) {
			unset($this->vars[$uKey]);
		}

		/**
		* @ignore
		*/
		public function view() {
			$uArgs = func_get_args();
			call_user_func_array('mvc::view', $uArgs);
		}

		/**
		 * @ignore
		 */
		public function viewFile() {
			$uArgs = func_get_args();
			call_user_func_array('mvc::viewFile', $uArgs);
		}
		
		/**
		* @ignore
		*/
		public function json() {
			$uArgs = func_get_args();
			call_user_func_array('mvc::json', $uArgs);
		}

		/**
		* @ignore
		*/
		public function redirect() {
			$uArgs = func_get_args();
			call_user_func_array('mvc::redirect', $uArgs);
		}

		/**
		* @ignore
		* @todo
		*/
		public function notfound() {
			$uArgs = func_get_args();
			call_user_func_array('mvc::notfound', $uArgs);
		}

		/**
		* @ignore
		*/
		public function error() {
			$uArgs = func_get_args();
			call_user_func_array('mvc::error', $uArgs);
		}

		/**
		* @ignore
		*/
		public function end() {
			framework::end(0);
		}
	}

	/**
	* ViewEngine: PHP
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*/
	class viewengine_php {
		/**
		* @ignore
		*/
		public static function renderview($uObject) {
			// variable extraction
			$model = &$uObject['model'];
			if(is_array($model)) {
				extract($model, EXTR_SKIP|EXTR_REFS);
			}

			if(isset($uObject['extra'])) {
				extract($uObject['extra'], EXTR_SKIP|EXTR_REFS);
			}

			require($uObject['templatePath'] . $uObject['templateFile']);
		}
	}

?>