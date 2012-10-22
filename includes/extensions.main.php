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
		public static $loaded = array();

		/**
		* Loads the extensions module.
		*/
		public static function load() {
			foreach(config::get(config::MAIN, '/extensionList', array()) as $tExtension) {
				$tPath = framework::translatePath(rtrim($tExtension, '/') . '/');
				$tConfiguration = config::loadConfiguration($tPath . 'extension.xml.php');

				if(count($tConfiguration) == 0) {
					continue;
				}

				self::$selected[] = $tConfiguration['/extension/name'];
				config::set($tConfiguration['/extension/name'], $tConfiguration);
				foreach($tConfiguration['/extension/includeList'] as &$tFile) {
					require_once($tPath . $tFile);
				}
			}		
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
				if(isset($tClassInfo['/extension/phpversion']) && !framework::phpVersion($tClassInfo['/extension/phpversion'])) {
					return false;
				}

				if(isset($tClassInfo['/extension/phpdependList'])) {
					foreach($tClassInfo['/extension/phpdependList'] as &$tExtension) {
						if(!extension_loaded($tExtension)) {
							throw new Exception('php extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}

				if(isset($tClassInfo['/extension/fwversion']) && !framework::version($tClassInfo['/extension/fwversion'])) {
					return false;
				}

				if(isset($tClassInfo['/extension/fwdependList'])) {
					foreach($tClassInfo['/extension/fwdependList'] as &$tExtension) {
						// if(!self::add($tExtension)) {
						if(!in_array($tExtension, self::$loaded)) {
							throw new Exception('framework extension is required - dependency: ' . $tExtension . ' for: ' . $uExtensionName);
						}
					}
				}
			}

			if(isset($tClassInfo['/extension/events/loadList'])) {
				foreach($tClassInfo['/extension/events/loadList'] as &$tLoad) {
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
