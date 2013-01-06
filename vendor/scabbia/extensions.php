<?php

	namespace Scabbia;

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
		public static $list;

		/**
		 * Loads the extensions module.
		 */
		public static function load() {
			$tExtensions = array();

			$tFiles = array();
			framework::glob(QPATH_SCABBIA . 'extensions/', null, GLOB_DIRECTORIES | GLOB_RECURSIVE, '', $tFiles);
			framework::glob(framework::$applicationPath . 'extensions/', null, GLOB_DIRECTORIES | GLOB_RECURSIVE, '', $tFiles);

			foreach($tFiles as $tFile) {
				if(!is_file($tFile . 'extension.xml.php')) {
					continue;
				}

				$tSubconfig = array();
				config::loadFile($tSubconfig, $tFile . 'extension.xml.php');

				$tName = $tSubconfig['/info/name'];

				if(isset($tSubconfig['/classList'])) {
					foreach($tSubconfig['/classList'] as $tClass) {
						self::$classmap[$tClass] = $tName;
					}
				}

				$tExtensions[$tName] = array('path' => $tFile, 'loaded' => 0, 'config' => $tSubconfig);
			}

			return $tExtensions;
		}

		/**
		 * Autoloader method.
		 *
		 */
		public static function autoloader($uClass) {
			if(isset(self::$classmap[$uClass]) && config::get('/options/autoload', '0') == '1') {
				self::loadExtension(self::$classmap[$uClass], true);

				return;
			}

			// throw new \Exception('class not found - ' . $uClass);
		}

		/**
		 * Loads the selected extensions.
		 *
		 * @uses loadExtension()
		 */
		public static function loadExtensions() {
			foreach(config::get('/extensionList', array()) as $tExtensionName) {
				self::loadExtension($tExtensionName, false);
				framework::$milestones[] = array('extension_' . $tExtensionName, microtime(true));
			}
		}

		/**
		 * Loads an extension.
		 *
		 * @param string $uExtensionName the extension
		 * @param bool $uAutoload
		 *
		 * @throws \Exception
		 * @return bool
		 */
		public static function loadExtension($uExtensionName, $uAutoload = false) {
			if(!isset(self::$list[$uExtensionName])) {
				return false;
			}

			if(self::$list[$uExtensionName]['loaded'] >= 1) {
				return true;
			}

			self::$list[$uExtensionName]['loaded'] = ($uAutoload) ? 2 : 1;
			$tClassInfo = self::$list[$uExtensionName]['config'];

			if(!COMPILED) {
				if(isset($tClassInfo['/includeList'])) {
					foreach($tClassInfo['/includeList'] as $tFile) {
						//! todo require_once?
						include(self::$list[$uExtensionName]['path'] . $tFile);
					}
				}

				if(isset($tClassInfo['/info/phpversion']) && !framework::phpVersion($tClassInfo['/info/phpversion'])) {
					return false;
				}

				if(isset($tClassInfo['/info/phpdependList'])) {
					foreach($tClassInfo['/info/phpdependList'] as $tExtension) {
						if(!extension_loaded($tExtension)) {
							throw new \Exception('php extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}

				if(isset($tClassInfo['/info/fwversion']) && !framework::version($tClassInfo['/info/fwversion'])) {
					return false;
				}

				if(isset($tClassInfo['/info/fwdependList'])) {
					foreach($tClassInfo['/info/fwdependList'] as $tExtension) {
						// if(!self::add($tExtension)) {
						if(!self::isLoaded($tExtension)) {
							throw new \Exception('framework extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}
			}

			if(isset($tClassInfo['/eventList'])) {
				foreach($tClassInfo['/eventList'] as $tLoad) {
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
		 * @param $uExtensionName
		 *
		 * @return bool load status.
		 */
		public static function isLoaded($uExtensionName) {
			return (isset(self::$list[$uExtensionName]) && self::$list[$uExtensionName]['loaded'] >= 1);
		}
	}

?>
