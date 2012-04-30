<?php

if(Extensions::isSelected('mvc')) {
	class mvc {
		public static $controller = null;
		public static $controllerActual = null;
		public static $controllerClass = null;
		public static $action = null;
		public static $actionActual = null;

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
			$tAutoRun = intval(Config::get('/mvc/@autorun', '1'));

			if($tAutoRun) {
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

			self::$controllerClass = new self::$controllerActual ();
			self::$controllerClass->{self::$actionActual}();

			// to interrupt event-chain execution
			return false;
		}

		public static function loadmodel($uModelClass) {
			return new $uModelClass (null);
		}

		public static function loadview() {
			$tViewNamePattern = Config::get('/mvc/view/@namePattern', '{@name}_{@device}_{@language}{@extension}');
			$tViewDefaultExtension = Config::get('/mvc/view/@defaultExtension', QEXT_PHP);

			$uArgs = func_get_args();
			$uModel = isset($uArgs[0]) ? $uArgs[0] : null;

			if(count($uArgs) == 2) {
				$tViewFile = $uArgs[1];
				$tViewExtension = '.' . pathinfo($tViewFile, PATHINFO_EXTENSION);
			}
			else {
				$tViewParameters = array(
					'controller' => isset($uArgs[1]) ? $uArgs[1] : mvc::$controller,
					'action' => isset($uArgs[2]) ? $uArgs[2] : mvc::$action,
					'device' => isset($uArgs[3]) ? $uArgs[3] : http::$crawlerType,
					'language' => isset($uArgs[4]) ? $uArgs[4] : i8n::$languageKey,
					'extension' => isset($uArgs[5]) ? $uArgs[5] : $tViewDefaultExtension
				);

				$tViewFile = string::format('{@controller}_{@action}_{@device}_{@language}{@extension}', $tViewParameters);
				$tViewExtension = $tViewParameters['extension'];
			}

			$tExtra = array(
				'root' => Framework::$siteroot,
				'lang' => i8n::$languageKey
			);

			Events::invoke('renderview', array(
				'viewFile' => &$tViewFile,
				'viewExtension' => &$tViewExtension,
				'model' => &$uModel,
				'extra' => &$tExtra
			));
		}
	}
	
	abstract class Model {
		public $controller;

		public function __construct($uController = null) {
			$this->controller = &$uController;
			$this->db = new DatabaseQuery(database::get()); // default database to member 'db'
		}

		public function loaddatabase($uDatabaseName, $uMemberName = null) {
			if(is_null($uMemberName)) {
				$uMemberName = $uDatabaseName;
			}

			$this->{$uMemberName} = new DatabaseQuery(database::get($uDatabaseName));
		}
	}

	abstract class Controller {
		public function loadmodel($uModelClass, $uMemberName = null) {
			if(is_null($uMemberName)) {
				$uMemberName = $uModelClass;
			}

			$this->{$uMemberName} = new $uModelClass ($this);
		}

		public function loadview() {
			$uArgs = func_get_args();
			call_user_func_array('mvc::loadview', $uArgs);
		}
	}
}

?>
