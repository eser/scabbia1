<?php
	
	class Config {
		public static $default = null;
		public static $development;
		public static $socket;

		public static function passScope(&$uNode) {
			if(isset($uNode['binding']) && !fnmatch((string)$uNode['binding'], self::$socket)) {
				return false;
			}

			if(isset($uNode['mode'])) {
				if((string)$uNode['mode'] == 'development') {
					if(!self::$development) {
						return false;
					}
				}
				else if(self::$development) {
					return false;
				}
			}

			return true;
		}

		public static function processChildrenAsArray($uNode, $uListElement, &$uContents) {
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

		public static function processChildren_r(&$uArray, &$uNodes, $uNode) {
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

		public static function &loadFiles($uFiles) {
			self::$development = file_exists(QPATH_APP . '/development');

			if(isset($_SERVER['SERVER_NAME'])) {
				self::$socket = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
			}
			else {
				self::$socket = 'localhost:80';
			}

			$tConfig = array();
			$tConfigNodes = array();

			foreach(glob3($uFiles, false, true) as $tFilename) {
				$tXmlDom = simplexml_load_file($tFilename, null, LIBXML_NOBLANKS|LIBXML_NOCDATA) or exit('Unable to read from config file - ' . $tFilename);
				self::processChildren_r($tConfig, $tConfigNodes, $tXmlDom);
			}

			return $tConfig;
		}
		
		public static function load() {
			self::$default = self::loadFiles(QPATH_APP . 'config/*');
		}
		
		public static function &get($uKey, $uDefault = null) {
			if(!array_key_exists($uKey, self::$default)) {
				return $uDefault;
			}

			return self::$default[$uKey];
		}
		
		public static function set($uVariable) {
			self::$default = $uVariable;
		}
		
		public static function dump() {
			var_dump(self::$default);
		}
		
		public static function export() {
			return var_export(self::$default, true);
		}
	}

?>
