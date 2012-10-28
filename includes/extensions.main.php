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
		public static $classmap = array();
		/**
		* @ignore
		*/
		public static $list = array();

		/**
		* Loads the extensions module.
		*/
		public static function load() {
			spl_autoload_register('extensions::autoloader');

			foreach(framework::glob(QPATH_CORE . 'extensions/', null, GLOB_DIRECTORIES|GLOB_RECURSIVE) as $tFile) {
				if(!is_file($tFile . 'extension.xml.php')) {
					continue;
				}

				$tConfiguration = array();
				config::loadFile($tConfiguration, $tFile . 'extension.xml.php');

				$tName = $tConfiguration['/info/name'];

				self::$list[$tName] = array('path' => $tFile, 'loaded' => false);

				if(isset($tConfiguration['/classList'])) {
					foreach($tConfiguration['/classList'] as &$tClass) {
						self::$classmap[$tClass] = $tName;
					}
				}

				config::set($tName, $tConfiguration);
			}
		}

		/**
		* Autoloader method.
		*
		*/
		public static function autoloader($uClass) {
			if(!isset(self::$classmap[$uClass])) {
				throw new Exception('class not found - ' . $uClass);
			}

			if(config::get(config::MAIN, '/options/autoload', '0') == '1') {
				self::loadExtension(self::$classmap[$uClass]);
				return;
			}

			// throw new Exception('class not found - ' . $uClass . ' but autoloading ' .  self::$classmap[$uClass] . ' extension is possible.');
		}

		/**
		* Loads the selected extensions.
		*
		* @uses loadExtension()
		*/
		public static function loadExtensions() {
			foreach(config::get(config::MAIN, '/extensionList', array()) as $tExtensionName) {
				self::loadExtension($tExtensionName);
				framework::$milestones[] = array('extension_' . $tExtensionName, microtime(true));
			}
		}

		/**
		* Adds an extension.
		*
		* @param string $uExtensionName the extension
		*/
		public static function loadExtension($uExtensionName) {
			if(!isset(self::$list[$uExtensionName])) {
				return false;
			}

			if(self::$list[$uExtensionName]['loaded']) {
				return true;
			}

			self::$list[$uExtensionName]['loaded'] = true;
			$tClassInfo = &config::$configurations[$uExtensionName];

			if(!COMPILED) {
				if(isset($tClassInfo['/includeList'])) {
					foreach($tClassInfo['/includeList'] as &$tFile) {
						//! todo
						include(self::$list[$uExtensionName]['path'] . $tFile);
					}
				}

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
						if(!self::isLoaded($tExtension)) {
							throw new Exception('framework extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}
			}

			if(isset($tClassInfo['/eventList'])) {
				foreach($tClassInfo['/eventList'] as &$tLoad) {
					if($tLoad['name'] == 'load') {
						call_user_func($tLoad['callback']);
						continue;
					}

					events::register($tLoad['name'], $tLoad['callback']);
				}
			}

			return true;
		}

		/**
		* Checks weather an extension is loaded or not.
		*
		* @return bool load status.
		*/
		public static function isLoaded($uExtensionName) {
			return (isset(self::$list[$uExtensionName]) && self::$list[$uExtensionName]['loaded']);
		}
	}

?>
