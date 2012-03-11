<?php
	
	class Config {
		private static $default = null;

		public static function processChildrenAsArray_r(&$uArray, $uNode, $tListElement = null) {
			foreach($uNode->children() as $tKey => $tNode) {
				if(!is_null($tListElement) && $tListElement == $tKey) {
					self::processChildrenAsArray_r($uArray[], $tNode, null);
				}
				else {
					if(substr($tKey, -4) == 'List') {
						self::processChildrenAsArray_r($uArray[$tKey], $tNode, substr($tKey, 0, -4));
					}
					else {
						self::processChildrenAsArray_r($uArray[$tKey], $tNode, null);
					}
				}
			}

			foreach($uNode->attributes() as $tKey => $tValue) {
				$uArray['@' . $tKey] = (string)$tValue;
			}

			$tNodeValue = rtrim((string)$uNode);
			if(strlen($tNodeValue) > 0) {
				$uArray['.'] = $tNodeValue;
			}
			else if($tListElement == null) {
				$uArray['.'] = null;
			}
			
//			if(count($uArray) == 1) {
//				$uArray = current($uArray);
//			}
		}

		public static function processChildren_r(&$uArray, $uPrefix, $uNode) {
			foreach($uNode->children() as $tKey => $tNode) {
				$tArrayKey = $uPrefix . '/' . $tKey;
				if(substr($tKey, -4) == 'List') {
					if(!isset($uArray[$tArrayKey]) || !is_array($uArray[$tArrayKey])) {
						$uArray[$tArrayKey] = array();
					}
					self::processChildrenAsArray_r($uArray[$tArrayKey], $tNode, substr($tKey, 0, -4));
					continue;
				}
				self::processChildren_r($uArray, $tArrayKey, $tNode);
			}

			foreach($uNode->attributes() as $tKey => $tValue) {
				$uArray[$uPrefix . '/@' . $tKey] = (string)$tValue;
			}

			$tNodeValue = rtrim((string)$uNode);
			if(strlen($tNodeValue) > 0) {
				$uArray[$uPrefix . '/.'] = $tNodeValue;
			}
		}

		public static function &loadFiles($uFiles) {
			if(isset($_SERVER['SERVER_NAME'])) {
				$tSocket = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
			}
			else {
				$tSocket = 'localhost:80';
			}
			$tXmlSource = '';

			foreach(glob($uFiles, GLOB_MARK|GLOB_NOSORT) as $tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				$tXml = simplexml_load_file($tFilename) or exit('Unable to read from config file - ' . $tFilename);

				// if(!is_null(self::$default)) {
				//	foreach(self::$default->children() as $tNode) {
				//		$tXmlSource .= $tNode->asXML();
				//	}
				// }

				if(isset($tXml->scope)) {
					foreach($tXml->scope as $tScope) {
						if(fnmatch((string)$tScope['binding'], $tSocket)) {
							foreach($tScope->children() as $tNode) {
								$tXmlSource .= $tNode->asXML();
							}
						}
					}
				} else {
					foreach($tXml->children() as $tNode) {
						$tXmlSource .= $tNode->asXML();
					}
				}
			}

			$tConfigDom = simplexml_load_string('<scabbia>' . $tXmlSource . '</scabbia>', null, LIBXML_NOBLANKS|LIBXML_NOCDATA);

			$tConfig = array();
			self::processChildren_r($tConfig, '', $tConfigDom);

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
