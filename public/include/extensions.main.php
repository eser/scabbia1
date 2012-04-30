<?php

	class Extensions {
		public static $selected = array();
		public static $loaded = array();

		public static function init() {
			$tExtensions = Config::get('/extensionList', array());

			foreach($tExtensions as &$tExtension) {
				self::$selected[] = $tExtension['@name'];
			}
		}

		public static function load() {
			foreach(self::$selected as &$tExtensionName) {
				self::add($tExtensionName);
			}
		}

		public static function run() {
			Events::invoke('run', array());
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

				if(isset($tClassInfo['phpdepends'])) {
					foreach($tClassInfo['phpdepends'] as &$tExtension) {
						if(!extension_loaded($tExtension)) {
							throw new Exception('php extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}

				if(isset($tClassInfo['fwversion']) && version_compare(SCABBIA_VERSION, $tClassInfo['fwversion'], '<')) {
					return false;
				}

				if(isset($tClassInfo['fwdepends'])) {
					foreach($tClassInfo['fwdepends'] as &$tExtension) {
						// if(!self::add($tExtension)) {
						if(!in_array($tExtension, self::$loaded)) {
							throw new Exception('framework extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}
			}

			if(method_exists($uExtensionName, 'extension_load')) {
				call_user_func(array($uExtensionName, 'extension_load'));
			}

			return true;
		}

		public static function isSelected($uExtensionName) {
			return in_array($uExtensionName, self::$selected);
		}
		
		public static function dump() {
			var_dump(self::$loaded);
		}
		
		public static function getAll() {
			return self::$loaded;
		}
	}

?>
