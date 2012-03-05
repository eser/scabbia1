<?php

	class Extensions {
		private static $loaded = array();
	
		public static function load() {
			$tExtensions = Config::get('/extensionList', array());
			foreach($tExtensions as &$tExtension) {
				self::add($tExtension['@name']);
			}
		}
		
		public static function add($uExtensionName) {
			if(in_array($uExtensionName, self::$loaded)) {
				return true;
			}

			if(!class_exists($uExtensionName)) {
				throw new Exception('extension class not loaded - ' . $uExtensionName);
			}

			self::$loaded[] = $uExtensionName;
			$tClassInfo = call_user_func(array($uExtensionName, 'extension_info'));

			if(!COMPILED) {
				if(isset($tClassInfo['phpversion']) && version_compare(PHP_VERSION, $tClassInfo['phpversion'], '<')) {
					return false;
				}

				if(isset($tClassInfo['fwversion']) && version_compare(SCABBIA_VERSION, $tClassInfo['fwversion'], '<')) {
					return false;
				}

				if(isset($tClassInfo['enabled']) && !$tClassInfo['enabled']) {
					return false;
				}

				if(isset($tClassInfo['depends'])) {
					foreach($tClassInfo['depends'] as &$tExtension) {
						// if(!self::add($tExtension)) {
						if(!in_array($tExtension, self::$loaded)) {
							throw new Exception('extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}
			}

			if(method_exists($uExtensionName, 'extension_load')) {
				call_user_func(array($uExtensionName, 'extension_load'));
			}

			return true;
		}
		
		public static function dump() {
			var_dump(self::$loaded);
		}
		
		public static function getAll() {
			return self::$loaded;
		}
	}
/*
	define('EXTENSION_EXTERNALCALL', true);
	define('EXTENSION_FUNCPREFIX', 'extension_');
	
	function scabbia_extensions() {
		if(isset(conf()->framework)) {
			if(!COMPILED) {
				foreach(conf()->framework->extension as $tExtension) {
					include(QPATH_CORE . (string)(conf()->framework->extensionDir['path']) . (string)$tExtension['name'] . QEXT_PHP);
				}
			}

			foreach(conf()->framework->extension as $tExtension) {
				ns()->add((string)$tExtension['name']);
			}
		}

		ns()->invoke('main');
		ns()->invoke('execRpcCommands');
	}

	class FwExtension {
		public $id;
		public $tag;

		public function __construct($uNamespace) {
			$this->id = $uNamespace;
			$this->tag = array();
		}

		public function __load() { // $extension->subextension->__load();
			ns()->add($this->id);
		}

	// overloading
		public function __get($uVariable) {
			if(property_exists(EXTENSION_FUNCPREFIX . $this->id, $uVariable)) {
				return ns()->tag['items'][$this->id]->{$uVariable};
			}

			return new FwExtension($this->id . '_' . $uVariable);
		}

		public function __set($uVariable, $uValue) {
			ns()->tag['items'][$this->id]->{$uVariable} = $uValue;
		}

		public function __call($uMethod, $uEventArgs) {
			ns()->eventDepth->push($this->id . '::' . $uMethod . '()');
			$tRet = call_user_func_array(array(&ns()->tag['items'][$this->id], $uMethod), $uEventArgs);
			ns()->eventDepth->pop();
			return $tRet;
		}

	// for array access, $items
		public function offsetExists($uId) {
			if(method_exists(EXTENSION_FUNCPREFIX . $this->id, 'offsetExists')) {
				return call_user_func_array(array(&ns()->tag['items'][$this->id], 'offsetExists'), array($uId));
			} else {
				return null;
			}
		}

		public function offsetGet($uId) {
			if(method_exists(EXTENSION_FUNCPREFIX . $this->id, 'offsetGet')) {
				return call_user_func_array(array(&ns()->tag['items'][$this->id], 'offsetGet'), array($uId));
			} else {
				return null;
			}
		}

		public function offsetSet($uId, $uValue) {
			if(method_exists(EXTENSION_FUNCPREFIX . $this->id, 'offsetSet')) {
				return call_user_func_array(array(&ns()->tag['items'][$this->id], 'offsetSet'), array($uId, $uValue));
			} else {
				return null;
			}
		}

		public function offsetUnset($uId) {
			if(method_exists(EXTENSION_FUNCPREFIX . $this->id, 'offsetUnset')) {
				return call_user_func_array(array(&ns()->tag['items'][$this->id], 'offsetUnset'), array($uId));
			} else {
				return null;
			}
		}

	// for iteration access
		public function getIterator() {
			if(method_exists(EXTENSION_FUNCPREFIX . $this->id, 'getIterator')) {
				return call_user_func_array(array(&ns()->tag['items'][$this->id], 'getIterator'), array());
			} else {
				return null; // new ArrayIterator(array())
			}
		}
	}

	class FwExtensionManagerCall {
		private $callFunc;

		function __construct($uCallFunc) {
			$this->callFunc = $uCallFunc;
		}

		function __call($uEventName, $uEventArgs = array()) {
			ns()->{$this->callFunc} ($uEventName, $uEventArgs);
		}
	}

	class FwExtensionManager extends CollectionBase {
		public $eventDepth;
		public $eventInvokes = true;
		public $globals;
		public $invoke;
		public $extensionExternalCall = EXTENSION_EXTERNALCALL;
		private $eventCallbacks;
		private $eventAuto;

		public function __construct() {
			parent::__construct();
			$this->eventDepth = new Collection();
			$this->globals = new Collection();
			$this->invoke = new FwExtensionManagerCall('invoke');
	//		$this->register = new FwExtensionManagerCall('register');
			$this->eventCallbacks = new Collection();
			$this->eventAuto = new Collection();
		}

		public function add($uName) {
			if($this->keyExists($uName)) { return; }

			if(!class_exists(EXTENSION_FUNCPREFIX . $uName)) {
				if($this->extensionExternalCall) {
					require(QPATH_CORE . (string)(conf()->framework->extensionDir['path']) . $uName . QEXT_PHP);
				} else {
					throw new Exception('extension class not loaded - ' . $uName);
				}
			}

			$tNameClass = EXTENSION_FUNCPREFIX . $uName;
			$tClassInfo = call_user_func(array($tNameClass, EXTENSION_FUNCPREFIX . 'info'));

//			if(isset($tClassInfo['phpversion']) && version_compare(PHP_VERSION, $tClassInfo['phpversion'], '<')) {
//				return;
//			}
			if(isset($tClassInfo['fwversion']) && version_compare(SCABBIA_VERSION, $tClassInfo['fwversion'], '<')) {
				return;
			}
			if(isset($tClassInfo['enabled']) && !$tClassInfo['enabled']) {
				return;
			}

			if(isset($tClassInfo['autoevents']) && $tClassInfo['autoevents']) {
				$this->eventAuto->add($uName);
			}
			if(isset($tClassInfo['depends'])) {
				foreach($tClassInfo['depends'] as &$tExtension) {
					$this->add($tExtension);
				}
			}

			$this->tag['items'][$uName] = new $tNameClass ();

			if(strpos($uName, '_') === false) { // only first-levels.
				$GLOBALS[$uName] = new FwExtension($uName);
				$this->globals->add('$' . $uName);
			}

			if(method_exists($tNameClass, EXTENSION_FUNCPREFIX . 'load')) {
				call_user_func(array(&$this->tag['items'][$uName], EXTENSION_FUNCPREFIX . 'load'));
			}
		}

		public function addKey($uKey, $uName) {
			return $this->add($uName);
		}

		public function register($uEventName, $uMixed) {
			if(!$this->eventCallbacks->keyExists($uEventName)) {
				$this->eventCallbacks->addKey($uEventName, new Collection());
			}
			if(is_object($uMixed)) {
				$this->eventCallbacks[$uEventName]->add(array(&$uMixed, EXTENSION_FUNCPREFIX . $uEventName));
			} else {
				$this->eventCallbacks[$uEventName]->add($uMixed);
			}
		}

		public function invoke($uEventName, $uEventArgs = array()) {
			if(!$this->eventInvokes) { return; }

			if($this->eventCallbacks->keyExists($uEventName)) {
				$tCallbacks = $this->eventCallbacks[$uEventName]->toArray();
			} else {
				$tCallbacks = array();
			}
			$tRet = array();

			foreach($this->eventAuto as $tExtName) {
				if(!method_exists(EXTENSION_FUNCPREFIX . $tExtName, EXTENSION_FUNCPREFIX . $uEventName)) { continue; }
				$tCallbacks[] = array(&$this->tag['items'][$tExtName], EXTENSION_FUNCPREFIX . $uEventName);
			}

			foreach($tCallbacks as &$tCallback) {
				if(is_array($tCallback)) {
					$tCallname = array(get_class($tCallback[0]), $tCallback[1]);
				} else {
					$tCallname = array('GLOBALS', $tCallback);
				}				

				$tKey = $tCallname[0] . '::' . $tCallname[1];
				$this->eventDepth->push($tKey . '()');
				$tRet[$tKey] = call_user_func($tCallback, $uEventArgs);
				$this->eventDepth->pop();
			}

			return $tRet;
		}

		public function getGlobalEval() {
			return 'global ' . $this->globals->toString(', ') . ';';
		}
	}

	function &ns($tReq = null) {
		if(isset($tReq)) { $tExt = new FwExtension($tReq); return $tExt; }
		return $GLOBALS['gExtensions'];
	}

	$GLOBALS['gExtensions'] = new FwExtensionManager();
*/
?>
