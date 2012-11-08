<?php

	/**
	 * Configuration class which handles all configuration-based operations
	 *
	 * @package Scabbia
	 * @subpackage Core
	 *
	 * @todo _node parsing
	 */
	class config {
		/**
		 * Defaul configuration
		 */
		public static $default;

		/**
		 * Loads the default configuration for the current application.
		 *
		 * @uses loadFile()
		 */
		public static function &load() {
			$tConfig = array();

			foreach(framework::glob(QPATH_CORE . 'config/', null, GLOB_RECURSIVE | GLOB_FILES) as $tFile) {
				self::loadFile($tConfig, $tFile);
			}

			foreach(framework::glob(framework::$applicationPath . 'config/', null, GLOB_RECURSIVE | GLOB_FILES) as $tFile) {
				self::loadFile($tConfig, $tFile);
			}

			return $tConfig;
		}

		/**
		 * @ignore
		 */
		private static function passScope(&$uNode) {
			if(isset($uNode['endpoint']) && (string)$uNode['endpoint'] != framework::$endpoint) {
				return false;
			}

			if(isset($uNode['mode'])) {
				if((string)$uNode['mode'] == 'development') {
					if(framework::$development < 1) {
						return false;
					}
				}
				else {
					if((string)$uNode['mode'] == 'debug') {
						if(framework::$development < 2) {
							return false;
						}
					}
					else {
						if(framework::$development >= 1) {
							return false;
						}
					}
				}
			}

			if(isset($uNode['module'])) {
				if((string)$uNode['module'] != framework::$module) {
					return false;
				}
			}

			if(isset($uNode['phpextension'])) {
				if(!extension_loaded((string)$uNode['phpextension'])) {
					return false;
				}
			}

			if(isset($uNode['phpversion'])) {
				if(!framework::phpVersion((string)$uNode['phpversion'])) {
					return false;
				}
			}

			return true;
		}

		/**
		 * @ignore
		 */
		private static function processChildrenAsArray($uNode, $uListElement, &$uContents) {
			foreach($uNode->children() as $tKey => $tNode) {
				if($tKey == 'scope') {
					if(!self::passScope($tNode)) {
						continue; // skip
					}

					self::processChildrenAsArray($tNode, $uListElement, $uContents);
					continue;
				}

				if(!is_null($uListElement) && $uListElement == $tKey) {
					self::processChildrenAsArray($tNode, null, $uContents[]);
				}
				else {
					if(substr($tKey, -4) == 'List') {
						if(!isset($uContents[$tKey])) {
							$uContents[$tKey] = array();
						}

						self::processChildrenAsArray($tNode, substr($tKey, 0, -4), $uContents[$tKey]);
					}
					else {
						if(!isset($uContents[$tKey])) {
							if($tNode->count() > 0) {
								$uContents[$tKey] = array();
							}
							else {
								$uContents[$tKey] = null;
							}
						}

						self::processChildrenAsArray($tNode, null, $uContents[$tKey]);
					}
				}
			}

			if($uNode->getName() == 'scope') {
				return;
			}

			$tNodeValue = rtrim((string)$uNode);
			if(strlen($tNodeValue) > 0) {
				if(count($uContents) > 0) {
					$uContents['.'] = $tNodeValue;
				}
				else {
					$uContents = $tNodeValue;
				}
			}
		}

		/**
		 * @ignore
		 */
		private static function processChildren_r(&$uArray, $uNode) {
			static $uNodes = array();
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
				foreach($uNode->children() as $tNode) {
					self::processChildren_r($uArray, $tNode);
				}

				if(!isset($tScope)) {
					$tNodeValue = rtrim((string)$uNode);
					if(strlen($tNodeValue) > 0) {
						$uArray[$tNodePath] = $tNodeValue;
					}
				}
			}

			if(!isset($tScope)) {
				array_pop($uNodes);
			}
		}

		/**
		 * Returns a configuration which is a compilation of a configuration file.
		 *
		 * @param $uConfig
		 * @param string $uFile path of configuration file
		 *
		 * @return array configuration
		 */
		public static function loadFile(&$uConfig, $uFile) {
			$tXmlDom = simplexml_load_file($uFile, null, LIBXML_NOBLANKS | LIBXML_NOCDATA) or exit('Unable to read from config file - ' . $uFile);
			self::processChildren_r($uConfig, $tXmlDom);
		}

		/**
		 * Gets a value from default configuration.
		 *
		 * @param string $uKey path of the value
		 * @param mixed $uDefault default value
		 * @return mixed|null
		 */
		public static function &get($uKey, $uDefault = null) {
			if(!array_key_exists($uKey, self::$default)) {
				return $uDefault;
			}

			return self::$default[$uKey];
		}
	}

?>