<?php
	
	/**
	* Configuration class which handles all configuration-based operations
	*
	* @package Scabbia
	* @subpackage Core
	*/
	class config {
		/**
		* Default configuration
		*/
		public static $default = null;

		/**
		* @ignore
		*/
		private static function passScope(&$uNode) {
			if(isset($uNode['binding']) && !fnmatch((string)$uNode['binding'], framework::$socket)) {
				return false;
			}

			if(isset($uNode['mode'])) {
				if((string)$uNode['mode'] == 'development') {
					if(!framework::$development) {
						return false;
					}
				}
				else if(framework::$development) {
					return false;
				}
			}

			return true;
		}

		/**
		* @ignore
		*/
		private static function processChildrenAsArray($uNode, $uListElement, &$uContents) {
			$tNodeName = $uNode->getName();

			foreach($uNode->children() as $tKey => $tNode) {
				if($tKey == 'scope') {
					if(!self::passScope($tNode)) {
						continue; // skip
					}

					self::processChildrenAsArray($tNode, null, $uContents);
					continue;
				}

				if(!is_null($uListElement) && $uListElement == $tKey) {
					self::processChildrenAsArray($tNode, null, $uContents[]);
				}
				else {
					if(!isset($uContents[$tKey])) {
						$uContents[$tKey] = array();
					}

					if(substr($tKey, -4) == 'List') {
						self::processChildrenAsArray($tNode, substr($tKey, 0, -4), $uContents[$tKey]);
					}
					else {
						self::processChildrenAsArray($tNode, null, $uContents[$tKey]);
					}
				}
			}

			foreach($uNode->attributes() as $tKey => $tValue) {
				$uContents['@' . $tKey] = (string)$tValue;
			}

			$tNodeValue = rtrim((string)$uNode);
			if(strlen($tNodeValue) > 0) {
				$uContents['.'] = $tNodeValue;
			}
		}

		/**
		* @ignore
		*/
		private static function processChildren_r(&$uArray, &$uNodes, $uNode) {
			$tNodeName = $uNode->getName();

			if($tNodeName == 'scope') {
				$tScope = true;

				if(!self::passScope($uNode)) {
					return; // skip
				}
			}

			if(!isset($tScope)) {
				array_push($uNodes, $tNodeName);
				$tNodePath = '/' . implode('/', array_slice($uNodes, 1));

				if(substr($tNodeName, -4) == 'List') {
					$tListName = substr($tNodeName, 0, -4);
				}
			}

			if(isset($tListName)) {
				if(!isset($uArray[$tNodePath])) {
					$uArray[$tNodePath] = array();
				}

				self::processChildrenAsArray($uNode, $tListName, $uArray[$tNodePath]);
			}
			else {
				foreach($uNode->children() as $tKey => $tNode) {
					self::processChildren_r($uArray, $uNodes, $tNode);
				}

				if(!isset($tScope)) {
					foreach($uNode->attributes() as $tKey => $tValue) {
						$uArray[$tNodePath . '/@' . $tKey] = (string)$tValue;
					}

					$tNodeValue = rtrim((string)$uNode);
					if(strlen($tNodeValue) > 0) {
						$uArray[$tNodePath . '/.'] = $tNodeValue;
					}
				}
			}

			if(!isset($tScope)) {
				array_pop($uNodes);
			}
		}

		/**
		* Returns a configuration array which is a compilation of multiple configuration files.
		*
		* @param string $uFiles path of configuration files
		* @return array configuration
		*/
		public static function &loadFiles($uFiles) {
			$tConfig = array();
			$tConfigNodes = array();

			foreach(glob3($uFiles, false, true) as $tFilename) {
				$tXmlDom = simplexml_load_file($tFilename, null, LIBXML_NOBLANKS|LIBXML_NOCDATA) or exit('Unable to read from config file - ' . $tFilename);
				self::processChildren_r($tConfig, $tConfigNodes, $tXmlDom);
			}

			return $tConfig;
		}

		/**
		* Loads the default configuration for the current application.
		*
		* @uses loadFiles()
		*/
		public static function load() {
			self::$default = self::loadFiles(framework::$applicationPath . 'config/*');
		}

		/**
		* Gets a value from default configuration.
		*
		* @param string $uKey path of the value
		* @param mixed $uDefault default value
		*/
		public static function &get($uKey, $uDefault = null) {
			if(!array_key_exists($uKey, self::$default)) {
				return $uDefault;
			}

			return self::$default[$uKey];
		}

		/**
		* Sets the default configuration for the current application.
		*
		* @param string $uVariable instance
		*/
		public static function set($uVariable) {
			self::$default = $uVariable;
		}

		/**
		* Outputs the default configuration.
		*/
		public static function dump() {
			var_dump(self::$default);
		}
		
		/**
		* Returns the default configuration as an array.
		*/
		public static function export() {
			return var_export(self::$default, true);
		}
	}

?>
