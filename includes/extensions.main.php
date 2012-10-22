<?php

	/**
	* Extensions manager which extends the framework capabilities with extra routines
	*
	* @package Scabbia
	* @subpackage Core
	*/
	class extensions {
		/**
		 * @ignore
		 */
		public static $selected = array();
		/**
		 * @ignore
		 */
		public static $classmap = array();
		/**
		* @ignore
		*/
		public static $loaded = array();

		/**
		* Loads the extensions module.
		*/
		public static function load() {
			spl_autoload_register('extensions::autoloader');

			foreach(config::get(config::MAIN, '/extensionList', array()) as $tExtension) {
				$tPath = framework::translatePath(rtrim($tExtension, '/') . '/');
				$tConfiguration = config::loadConfiguration($tPath . 'extension.xml.php');

				if(count($tConfiguration) == 0) {
					continue;
				}

				$tName = $tConfiguration['/info/name'];

				self::$selected[] = $tName;
				config::set($tName, $tConfiguration);
				if(isset($tConfiguration['/includeList'])) {
					foreach($tConfiguration['/includeList'] as &$tFile) {
						require_once($tPath . $tFile);
					}
				}

				if(isset($tConfiguration['/classList'])) {
					foreach($tConfiguration['/classList'] as &$tClass) {
						self::$classmap[$tClass] = $tName;
					}
				}
			}
		}

		/**
		* Autoloader method.
		* 
		*/
		public static function autoloader($uClass) {
			if(isset(self::$classmap[$uClass])) {
				throw new Exception('class not found - ' . $uClass . ' but autoload is possible.');
			}

			throw new Exception('class not found - ' . $uClass);
		}
		
		/**
		* Loads the selected extensions.
		*
		* @uses loadExtension()
		*/
		public static function loadExtensions() {
			foreach(self::$selected as &$tExtensionName) {
				self::loadExtension($tExtensionName);
			}
		}

		/**
		* Adds an extension.
		*
		* @param string $uExtensionName the extension
		*/
		public static function loadExtension($uExtensionName) {
			if(in_array($uExtensionName, self::$loaded)) {
				return true;
			}

			self::$loaded[] = $uExtensionName;
			$tClassInfo = &config::$configurations[$uExtensionName];

			if(!COMPILED) {
				if(isset($tClassInfo['/info/phpversion']) && !framework::phpVersion($tClassInfo['/info/phpversion'])) {
					return false;
				}

				if(isset($tClassInfo['/info/phpdependList'])) {
					foreach($tClassInfo['/info/phpdependList'] as &$tExtension) {
						if(!extension_loaded($tExtension)) {
							throw new Exception('php extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}

				if(isset($tClassInfo['/info/fwversion']) && !framework::version($tClassInfo['/info/fwversion'])) {
					return false;
				}

				if(isset($tClassInfo['/info/fwdependList'])) {
					foreach($tClassInfo['/info/fwdependList'] as &$tExtension) {
						// if(!self::add($tExtension)) {
						if(!in_array($tExtension, self::$loaded)) {
							throw new Exception('framework extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}
			}

			if(isset($tClassInfo['/events/loadList'])) {
				foreach($tClassInfo['/events/loadList'] as &$tLoad) {
					call_user_func($tLoad);
				}
			}

			return true;
		}

		/**
		* Checks weather an extension is selected or not.
		*
		* @return bool selection status.
		*/
		public static function isSelected($uExtensionName) {
			return in_array($uExtensionName, self::$selected);
		}
	}

?>
