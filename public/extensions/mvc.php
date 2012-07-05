<?php

if(extensions::isSelected('mvc')) {
	/**
	* MVC Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class mvc {
		public static $controller = null;
		public static $controllerActual = null;
		public static $controllerClass = null;
		public static $action = null;
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
				'fwdepends' => array('string', 'http', 'i8n', 'database')
			);
		}
		
		public static function extension_load() {
			self::$defaultController = config::get('/mvc/routing/@defaultController', 'home');
			self::$defaultAction = config::get('/mvc/routing/@defaultAction', 'index');

			$tAutoRun = intval(config::get('/mvc/@autorun', '1'));

			if($tAutoRun) {
				events::register('run', events::Callback('mvc::run'));
			}
		}

		public static function run() {
			$tNotfoundController = config::get('/mvc/routing/@notfoundController', 'home');
			$tNotfoundAction = config::get('/mvc/routing/@notfoundAction', 'notfound');

			$tControllerUrlKey = config::get('/mvc/routing/@controllerUrlKey', '0');
			$tActionUrlKey = config::get('/mvc/routing/@actionUrlKey', '1');

			if(array_key_exists($tControllerUrlKey, $_GET) && strlen($_GET[$tControllerUrlKey]) > 0) {
				self::$controller = $_GET[$tControllerUrlKey];
			}
			else {
				self::$controller = self::$defaultController;
			}
			
			if(array_key_exists($tActionUrlKey, $_GET) && strlen($_GET[$tActionUrlKey]) > 0) {
				self::$action = $_GET[$tActionUrlKey];
			}
			else {
				self::$action = self::$defaultAction;
			}

			self::$controllerActual = self::$controller;
			
			if(count($_POST) > 0 && method_exists(self::$controller, self::$action . '_post')) {
				self::$actionActual = self::$action . '_post';
			}
			else {
				self::$actionActual = self::$action;
			}

			events::invoke('routing', array(
				'controller' => &self::$controller,
				'action' => &self::$action,
				'controllerActual' => &self::$controllerActual,
				'actionActual' => &self::$actionActual
			));

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

			$tParms = http::getParameterSegments();

			self::$controllerClass = new self::$controllerActual ();
			call_user_func_array(array(&self::$controllerClass, self::$actionActual), $tParms);
			
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
			$uModel = isset($uArgs[0]) ? $uArgs[0] : null;

			if(count($uArgs) == 2) {
				$tViewFile = $uArgs[1];
				$tViewExtension = '.' . pathinfo($tViewFile, PATHINFO_EXTENSION);
			}
			else {
				$tViewParameters = array(
					'controller' => isset($uArgs[1]) ? $uArgs[1] : self::$controller,
					'action' => isset($uArgs[2]) ? $uArgs[2] : self::$action,
					'device' => isset($uArgs[3]) ? $uArgs[3] : http::$crawlerType,
					'language' => isset($uArgs[4]) ? $uArgs[4] : i8n::$languageKey,
					'extension' => isset($uArgs[5]) ? $uArgs[5] : $tViewDefaultExtension
				);

				$tViewFile = string::format($tViewNamePattern, $tViewParameters);
				$tViewExtension = $tViewParameters['extension'];
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

		public static function json($uObject, $uOptions = 0) {
			echo json_encode($uObject, $uOptions);
		}

		private static function url_internal($uArgs) {
			$tQueryStringPattern = config::get('/mvc/routing/@queryStringPattern', '{@siteroot}/{@controller}/{@action}{@queryString}');

			if(count($uArgs) == 1) {
				if(!is_array($uArgs[0])) {
					return $uArgs[0];
				}

				$tQueryParameters = array();
				$tQueryParameters['siteroot'] = string::coalesce(array($uArgs[0], 'siteroot'), framework::$siteroot);
				$tQueryParameters['controller'] = string::coalesce(array($uArgs[0], 'controller'), self::$controller, self::$defaultController);
				$tQueryParameters['action'] = string::coalesce(array($uArgs[0], 'action'), self::$defaultAction);
				$tQueryParameters['device'] = string::coalesce(array($uArgs[0], 'device'), http::$crawlerType);
				$tQueryParameters['language'] = string::coalesce(array($uArgs[0], 'language'), i8n::$languageKey);
				$tQueryParameters['queryString'] = string::coalesce(array($uArgs[0], 'queryString'), '');
			}
			else {
				$tQueryParameters = array();
				$tQueryParameters['siteroot'] = string::coalesce(framework::$siteroot);
				$tQueryParameters['controller'] = string::coalesce(array($uArgs, 0), self::$controller, self::$defaultController);
				$tQueryParameters['action'] = string::coalesce(array($uArgs, 1), self::$defaultAction);
				$tQueryParameters['device'] = string::coalesce(array($uArgs, 2), http::$crawlerType);
				$tQueryParameters['language'] = string::coalesce(array($uArgs, 3), i8n::$languageKey);
				$tQueryParameters['queryString'] = string::coalesce(array($uArgs, 4), '');
			}

			return string::format($tQueryStringPattern, $tQueryParameters);
		}

		public static function url() {
			return self::url_internal(func_get_args());
		}
		
		public static function redirect() {
			$tQuery = self::url_internal(func_get_args());
			http::sendRedirect($tQuery, true);
		}
	}
	
	/**
	* Model Class
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	abstract class model {
		public $controller;

		public function __construct($uController = null) {
			$this->controller = &$uController;
			$this->db = new databaseQuery(database::get()); // default database to member 'db'
		}

		public function loaddatabase($uDatabaseName, $uMemberName = null) {
			if(is_null($uMemberName)) {
				$uMemberName = $uDatabaseName;
			}

			$this->{$uMemberName} = new databaseQuery(database::get($uDatabaseName));
		}
	}

	/**
	* Controller Class
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	abstract class controller {
		public $defaultView;

		public function loadmodel($uModelClass, $uMemberName = null) {
			if(is_null($uMemberName)) {
				$uMemberName = $uModelClass;
			}

			$this->{$uMemberName} = new $uModelClass ($this);
		}

		public function view() {
			$uArgs = func_get_args();

			switch(count($uArgs)) {
			case 0:
				$uArgs[] = null;
				$uArgs[] = $this->defaultView;
				break;
			case 1:
				$uArgs[] = $this->defaultView;
				break;
			}

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

		public function error($uMessage) {
			$tViewbag = array(
				'title' => 'Error',
				'message' => $uMessage
			);

			call_user_func('mvc::view', $tViewbag, 'shared_error.cshtml');
			exit(1);
		}

		public function end() {
			exit(0);
		}
	}
}

?>
