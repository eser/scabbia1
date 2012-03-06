<?php

	class mvc {
		private static $controller = null;
		private static $controllerActual = null;
		private static $controllerClass = null;
		private static $action = null;
		private static $actionActual = null;

		public static function extension_info() {
			return array(
				'name' => 'mvc',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'fwversion' => '1.0',
				'enabled' => true,
				'autoevents' => false,
				'depends' => array('string', 'http', 'database')
			);
		}
		
		public static function extension_load() {
			if(COMPILED) {
				Events::register('run', Events::Callback('mvc::run'));
			}
		}

		public static function run() {
			$tDefaultController = Config::get('/mvc/routing/@defaultController', 'home');
			$tDefaultAction = Config::get('/mvc/routing/@defaultAction', 'index');

			$tNotfoundController = Config::get('/mvc/routing/@notfoundController', 'home');
			$tNotfoundAction = Config::get('/mvc/routing/@notfoundAction', 'notfound');

			$tControllerUrlKey = Config::get('/mvc/routing/@controllerUrlKey', '0');
			$tActionUrlKey = Config::get('/mvc/routing/@actionUrlKey', '1');

			if(array_key_exists($tControllerUrlKey, $_GET) && strlen($_GET[$tControllerUrlKey]) > 0) {
				self::$controller = $_GET[$tControllerUrlKey];
			}
			else {
				self::$controller = $tDefaultController;
			}
			
			if(array_key_exists($tActionUrlKey, $_GET) && strlen($_GET[$tActionUrlKey]) > 0) {
				self::$action = $_GET[$tActionUrlKey];
			}
			else {
				self::$action = $tDefaultAction;
			}

			self::$controllerActual = self::$controller;
			
			if(count($_POST) > 0 && method_exists(self::$controller, self::$action . '_post')) {
				self::$actionActual = self::$action . '_post';
			}
			else {
				self::$actionActual = self::$action;
			}

			Events::invoke('routing', array(
				'controller' => &self::$controller,
				'action' => &self::$action,
				'controllerActual' => &self::$controllerActual,
				'actionActual' => &self::$actionActual
			));

			if(!method_exists(self::$controllerActual, self::$actionActual)) { // !class_exist(self::$controller) || 
				self::$controllerActual = $tNotfoundController;
				self::$actionActual = $tNotfoundAction;
			}

			self::$controllerClass = new self::$controllerActual ();
			self::$controllerClass->{self::$actionActual}();
		}

		public static function getController() {
			return self::$controller;
		}

		public static function getAction() {
			return self::$action;
		}
	}
	
	abstract class Model {
		protected $controller;
		protected $db;

		public function __construct(&$uController) {
			$this->controller = &$uController;
			$this->db = new DatabaseQuery();
		}
	}

	abstract class Controller {
		protected $device = '';
		protected $language = '';

		public function loadmodel($uModelClass, $uMemberName = null) {
			if(is_null($uMemberName)) {
				$uMemberName = $uModelClass;
			}

			$this->{$uMemberName} = new $uModelClass ($this);
		}

		public function loadview($uViewFile, $uModel = null) {
			$tViewFile = pathinfo($uViewFile, PATHINFO_FILENAME);
			$tViewExtension = pathinfo($uViewFile, PATHINFO_EXTENSION);

			if(strlen($this->device) > 0) {
				$tViewFile .= '.' . $this->device;
			}

			if(strlen($this->language) > 0) {
				$tViewFile .= '.' . $this->language;
			}

			Events::invoke('renderview', array(
				'viewFile' => &$tViewFile,
				'viewExtension' => &$tViewExtension,
				'model' => &$uModel
			));
		}

		public function getDevice() {
			return $this->device;
		}

		public function getLanguage() {
			return $this->language;
		}

		public function httpGet($uKey, $uDefault = '', $uFilter = null) {
			if(!array_key_exists($uKey, $_GET)) {
				return $uDefault;
			}

			if(!is_null($uFilter)) {
				return string::filter($_GET[$uKey], $uFilter);
			}

			return $_GET[$uKey];
		}

		public function httpPost($uKey, $uDefault = null) {
			if(!array_key_exists($uKey, $_POST)) {
				return $uDefault;
			}

			return $_POST[$uKey];
		}

		public function httpCookie($uKey, $uDefault = null) {
			if(!array_key_exists($uKey, $_COOKIE)) {
				return $uDefault;
			}

			return $_COOKIE[$uKey];
		}
	}

?>
