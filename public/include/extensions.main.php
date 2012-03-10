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

?>
