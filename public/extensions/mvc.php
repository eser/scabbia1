<?php

if(extensions::isSelected('mvc')) {
	/**
	* MVC Extension
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*
	* @todo remove underscore '_' in controller, action names
	* @todo forbid 'shared' for controller names
	*/
	class mvc {
		public static $route = null;
		public static $controllerActual = null;
		public static $controllerStack = null;
		public static $actionActual = null;
		public static $defaultController = null;
		public static $defaultAction = null;

		public static function extension_info() {
			return array(
				'name' => 'mvc',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string', 'arrays', 'http', 'i8n', 'database')
			);
		}

		public static function extension_load() {
			self::$defaultController = config::get('/mvc/routes/@defaultController', 'home');
			self::$defaultAction = config::get('/mvc/routes/@defaultAction', 'index');

			$tAutoRun = intval(config::get('/mvc/@autorun', '1'));

			if($tAutoRun) {
				events::register('run', events::Callback('mvc::run'));
			}
		}

		protected static function &getControllerData($uController) {
			$tControllerData = array(
				'actionUrlKeys' => config::get('/mvc/routes/@actionUrlKeys', '1'),
				'defaultAction' => self::$defaultAction,
				'link' => config::get('/mvc/routes/@link', '{@siteroot}/{@controller}/{@action}{@queryString}')
			);

			foreach(config::get('/mvc/controllerList', array()) as $tController) {
				if($uController != $tController['@name']) {
					continue;
				}

				if(isset($tController['@actionUrlKeys'])) {
					$tControllerData['actionUrlKeys'] = $tController['@actionUrlKeys'];
				}

				if(isset($tController['@defaultAction'])) {
					$tControllerData['defaultAction'] = $tController['@defaultAction'];
				}

				if(isset($tController['@link'])) {
					$tControllerData['link'] = $tController['@link'];
				}

				break;
			}

			return $tControllerData;
		}

		public static function findRoute($uArgs) {
			if(!is_array($uArgs)) {
				$uArgs = http::parseGet($uArgs);
			}

			$tControllerUrlKey = config::get('/mvc/routes/@controllerUrlKey', '0');

			$tRoute = array(
				'queryString' => $uArgs
			);

			if(array_key_exists($tControllerUrlKey, $tRoute['queryString']['segments']) && strlen($tRoute['queryString']['segments'][$tControllerUrlKey]) > 0) {
				$tRoute['controller'] = $tRoute['queryString']['segments'][$tControllerUrlKey];
				unset($tRoute['queryString']['segments'][$tControllerUrlKey]);
			}
			else {
				$tRoute['controller'] = self::$defaultController;
			}

			$tControllerData = self::getControllerData($tRoute['controller']);

			$tActionKeys = explode(',', $tControllerData['actionUrlKeys']);
			$tRoute['action'] = '';

			foreach($tActionKeys as $tActionKey) {
				if(!isset($tRoute['queryString']['segments'][$tActionKey])) {
					break;
				}

				if(strlen($tRoute['action']) > 0) {
					$tRoute['action'] .= '_';
				}
				
				$tRoute['action'] .= $tRoute['queryString']['segments'][$tActionKey];
				unset($tRoute['queryString']['segments'][$tActionKey]);
			}

			if(strlen($tRoute['action']) == 0) {
				$tRoute['action'] = $tControllerData['defaultAction'];
			}

			return $tRoute;
		}

		public static function run() {
			self::$route = self::findRoute($_GET);
			self::$controllerActual = self::$route['controller'];
			
			if(http::$isPost && method_exists(self::$route['controller'], self::$route['action'] . '_post')) {
				self::$actionActual = self::$route['action'] . '_post';
			}
			else if(http::$isAjax && method_exists(self::$route['controller'], self::$route['action'] . '_ajax')) {
				self::$actionActual = self::$route['action'] . '_ajax';
			}
			else {
				self::$actionActual = self::$route['action'];
			}

			$tParameterSegments = null;
			events::invoke('routing', array(
				'controller' => &self::$route['controller'],
				'action' => &self::$route['action'],
				'controllerActual' => &self::$controllerActual,
				'actionActual' => &self::$actionActual,
				'parameterSegments' => &$tParameterSegments
			));

			$tNotfoundController = config::get('/mvc/routes/@notfoundController', 'home');
			$tNotfoundAction = config::get('/mvc/routes/@notfoundAction', 'notfound');

			try {
				$tReflectionMethod = new ReflectionMethod(self::$controllerActual, self::$actionActual);

				if(!$tReflectionMethod->isPublic()) {
					self::$controllerActual = $tNotfoundController;
					self::$actionActual = $tNotfoundAction;
				}
			}
			catch(ReflectionException $ex) {
				self::$controllerActual = $tNotfoundController;
				self::$actionActual = $tNotfoundAction;
			}

			self::$controllerStack = array();

			$tController = new self::$controllerActual ();

			array_push(self::$controllerStack, $tController);
			call_user_func_array(array(&$tController, self::$actionActual), self::$route['queryString']['segments']);
			array_pop(self::$controllerStack);

			// to interrupt event-chain execution
			return false;
		}

		public static function loadmodel($uModelClass) {
			return new $uModelClass (null);
		}

		public static function view() {
			$tViewNamePattern = config::get('/mvc/view/@namePattern', '{@controller}_{@action}_{@device}_{@language}{@extension}');
			$tViewDefaultExtension = config::get('/mvc/view/@defaultExtension', QEXT_PHP);

			$uArgs = func_get_args();
			$uArgsCount = count($uArgs);

			$uController = end(self::$controllerStack);
			$uView = ($uArgsCount >= 1) ? $uArgs[0] : $uController->defaultView;
			$uModel = ($uArgsCount >= 2) ? $uArgs[1] : $uController->vars;

			if(is_string($uView)) {
				$tViewFile = $uView;
				$tViewExtension = '.' . pathinfo($tViewFile, PATHINFO_EXTENSION);
			}
			else {
				if(is_null($uView)) {
					$uView = array();
				}
				else if(!is_array($uView)) {
					return;
				}

				if(!isset($uView['controller'])) {
					$uView['controller'] = self::$route['controller'];
				}
				if(!isset($uView['action'])) {
					$uView['action'] = self::$route['action'];
				}
				if(!isset($uView['device'])) {
					$uView['device'] = http::$crawlerType;
				}
				if(!isset($uView['language'])) {
					$uView['language'] = i8n::$languageKey;
				}
				if(!isset($uView['extension'])) {
					$uView['extension'] = $tViewDefaultExtension;
				}

				$tViewFile = string::format($tViewNamePattern, $uView);
				$tViewExtension = $uView['extension'];
			}

			$tExtra = array(
				'root' => framework::$siteroot,
				'lang' => i8n::$languageKey
			);

			events::invoke('renderview', array(
				'viewFile' => &$tViewFile,
				'viewExtension' => &$tViewExtension,
				'model' => &$uModel,
				'extra' => &$tExtra
			));
		}

		public static function json() {
			$uArgs = func_get_args();
			$uArgsCount = count($uArgs);

			$uController = end(self::$controllerStack);
			$uModel = ($uArgsCount >= 1) ? $uArgs[0] : $uController->vars;
			$uOptions = ($uArgsCount >= 2) ? $uArgs[1] : 0;

			http::sendHeader('Content-Type', 'application/json', true);
			echo json_encode(
				array(
					'isSuccess' => true,
					'errorMessage' => null,
					'object' => &$uModel
				),
				$uOptions
			);
		}

		public static function error($uMessage) {
			$tViewbag = array(
				'title' => 'Error',
				'message' => $uMessage
			);

			self::view('shared_error.cshtml', $tViewbag);
			exit(1);
		}

		private static function url_internal($uArgs) {
			$tSegments = self::findRoute($uArgs);
			$tArray = array(
				'siteroot' => framework::$siteroot,
				'device' => http::$crawlerType,
				'language' => i8n::$languageKey,
				'controller' => $tSegments['controller'],
				'action' => $tSegments['action'],
				'queryString' =>  http::buildQueryString($tSegments['queryString'])
			);

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
				$tQueryParameters['language'] = string::coalesce(array($uArgs[0], 'language'), i8n::$languageKey);
				$tQueryParameters['queryString'] = string::coalesce(array($uArgs[0], 'queryString'), '');
			}
			else {
				$tQueryParameters = array();
				$tQueryParameters['siteroot'] = framework::$siteroot;
				$tQueryParameters['controller'] = $uArgs[0];

				$tControllerData = self::getControllerData($tQueryParameters['controller']);

				$tQueryParameters['action'] = string::coalesce(array($uArgs, 1), self::$defaultAction);
				$tQueryParameters['device'] = string::coalesce(array($uArgs, 2), http::$crawlerType);
				$tQueryParameters['language'] = string::coalesce(array($uArgs, 3), i8n::$languageKey);
				$tQueryParameters['queryString'] = string::coalesce(array($uArgs, 4), '');
			}

			return string::format($tControllerData['link'], $tQueryParameters);
*/
		}

		public static function url() {
			$tArgs = func_get_args();
			return call_user_func_array('mvc::url_internal', $tArgs);
		}
		
		public static function redirect() {
			$tArgs = func_get_args();
			$tQuery = call_user_func_array('mvc::url_internal', $tArgs);

			http::sendRedirect($tQuery, true);
		}

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

					if($uAjaxOnly && substr($tMethod->name, -5) != '_ajax') {
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

		public static function exportAjaxJs() {
			$tArray = self::export(true);
			
			echo <<<EOD
<script type="text/javascript">
	$(document).ready(function() {
		var ajaxObj = {};
		if(typeof pageAjaxStart == 'function') {
			ajaxObj.beforeSend = pageAjaxStart;
		}

		if(typeof pageAjaxEnd == 'function') {
			ajaxObj.complete = pageAjaxEnd;
		}

		$.ajaxSetup(ajaxObj);

		$.extend({
			helpers: {
				sendAjax: function(path, values, fnc) {
					$.ajax({
						type: 'POST',
						url: path,
						data: values,
						success: function(data) {
							if (!data.isSuccess) {
								// $.helpers.msgbox(5, 'Error: ' + data.errorMessage);
								alert(data.errorMessage);
								return;
							}

							fnc(data.object);
						},
						datatype: 'json'
					});
				}
			}
EOD;
		foreach($tArray as $tClassName => $tClass) {
			$tLines = array();
			
			echo ',', "\r\n\t\t\t", $tClassName, ': {', "\r\n";

			foreach($tClass as $tMethod) {
				$tMethod = substr($tMethod, 0, -5);
				$tLines[] = "\t\t\t\t" . $tMethod . ': function(values, fnc) { $.helpers.sendAjax(\'' . self::url($tClassName . '/' . strtr($tMethod, '_', '/')) . '\', values, fnc); }';
			}
			echo implode(',' . "\r\n", $tLines), "\r\n\t\t\t";
			echo '}';
		}
echo <<<EOD

		});
	});
</script>
EOD;
		}
	}
	
	/**
	* Model Class
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*/
	abstract class model {
		public $controller;
		public $db;

		public function __construct($uController = null) {
			$this->controller = &$uController;
			$this->db = database::get(); // default database to member 'db'
		}

		public function loaddatabase($uDatabaseName, $uMemberName = null) {
			if(is_null($uMemberName)) {
				$uMemberName = $uDatabaseName;
			}

			$this->{$uMemberName} = database::get($uDatabaseName);
		}
	}

	/**
	* Controller Class
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*/
	abstract class controller {
		public $defaultView = null;
		public $db;
		public $vars;

		public function __construct() {
			$this->db = database::get(); // default database to member 'db'
			$this->vars = array();
		}

		public function loaddatabase($uDatabaseName, $uMemberName = null) {
			if(is_null($uMemberName)) {
				$uMemberName = $uDatabaseName;
			}

			$this->{$uMemberName} = database::get($uDatabaseName);
		}

		public function loadmodel($uModelClass, $uMemberName = null) {
			if(is_null($uMemberName)) {
				$uMemberName = $uModelClass;
			}

			$this->{$uMemberName} = new $uModelClass ($this);
		}

		public function get($uKey) {
			return $this->vars[$uKey];
		}

		public function set($uKey, $uValue) {
			$this->vars[$uKey] = $uValue;
		}

		public function setRef($uKey, &$uValue) {
			$this->vars[$uKey] = &$uValue;
		}

		public function remove($uKey) {
			unset($this->vars[$uKey]);
		}

		public function view() {
			$uArgs = func_get_args();
			call_user_func_array('mvc::view', $uArgs);
		}

		public function json() {
			$uArgs = func_get_args();
			call_user_func_array('mvc::json', $uArgs);
		}

		public function redirect() {
			$uArgs = func_get_args();
			call_user_func_array('mvc::redirect', $uArgs);
		}

		public function error() {
			$uArgs = func_get_args();
			call_user_func_array('mvc::error', $uArgs);
		}

		public function end() {
			exit(0);
		}
	}
}

?>
