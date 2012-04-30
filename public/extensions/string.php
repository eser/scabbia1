<?php

if(Extensions::isSelected('string')) {
	class string {
		public static function extension_info() {
			return array(
				'name' => 'string',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}
		
		public static function coalesce() {
			foreach(func_get_args() as $tValue) {
				if(!is_null($tValue)) {
					return $tValue;
				}
			}
		}

		public static function format($uString) {
			$uArgs = func_get_args();
			array_shift($uArgs);

			if(count($uArgs) > 0 && is_array($uArgs[0])) {
				$uArgs = $uArgs[0];
			}

			$tBrackets = array(array(null, ''));
			$tQuoteChar = false;
			$tLastItem = 0;
			$tArrayItem = 1;

			for($tPos = 0, $tLen = strlen($uString);$tPos < $tLen;$tPos++) {
				if($uString[$tPos] == '\\') {
					$tBrackets[$tLastItem][$tArrayItem] .= $uString[++$tPos];
					continue;
				}

				if($tQuoteChar === false && $uString[$tPos] == '{') {
					$tLastItem++;
					$tBrackets[$tLastItem] = array(null, null);
					$tArrayItem = 1;
					continue;
				}

				if($tLastItem > 0) {
					if(is_null($tBrackets[$tLastItem][$tArrayItem])) {
						if($uString[$tPos] == '\'' || $uString[$tPos] == '"') {
							$tQuoteChar = $uString[$tPos];
							$tBrackets[$tLastItem][$tArrayItem] = '"';	// static text
							$tPos++;
						}
						else if($uString[$tPos] == '!') {
							$tBrackets[$tLastItem][$tArrayItem] = '!';	// dynamic text
							$tPos++;
						}
						else if($uString[$tPos] == '@') {
							$tBrackets[$tLastItem][$tArrayItem] = '@';	// parameter
							$tPos++;
						}
						else {
							$tBrackets[$tLastItem][$tArrayItem] = '@';	// parameter
						}
					}

					if($tBrackets[$tLastItem][$tArrayItem][0] == '"') {
						if($tQuoteChar == $uString[$tPos]) {
							$tQuoteChar = false;
							continue;
						}

						if($tQuoteChar !== false) {
							$tBrackets[$tLastItem][$tArrayItem] .= $uString[$tPos];
							continue;
						}

						if($uString[$tPos] != ',' && $uString[$tPos] != '}') {
							continue;
						}
					}

					if($tArrayItem == 1 && $uString[$tPos] == '|' && is_null($tBrackets[$tLastItem][0])) {
						$tBrackets[$tLastItem][0] = $tBrackets[$tLastItem][1];
						$tBrackets[$tLastItem][1] = null;
						continue;
					}

					if($uString[$tPos] == ',') {
						$tBrackets[$tLastItem][++$tArrayItem] = null;
						continue;
					}

					if($uString[$tPos] == '}') {
						$tFunc = array_shift($tBrackets[$tLastItem]);
						foreach($tBrackets[$tLastItem] as &$tItem) {
							switch($tItem[0]) {
							case '"':
								$tItem = substr($tItem, 1);
								break;
							case '@':
								$tItem = $uArgs[substr($tItem, 1)];
								break;
							case '!':
								$tItem = constant(substr($tItem, 1));
								break;
							}
						}

						if(!is_null($tFunc)) {
							$tString = call_user_func_array(substr($tFunc, 1), $tBrackets[$tLastItem]);
						}
						else {
							$tString = implode(', ', $tBrackets[$tLastItem]);
						}

						$tArrayItem = count($tBrackets[$tLastItem - 1]) - 1;
						$tBrackets[$tLastItem - 1][$tArrayItem] .= $tString;
						unset($tBrackets[$tLastItem]);
						$tLastItem--;

						continue;
					}
				}

				$tBrackets[$tLastItem][$tArrayItem] .= $uString[$tPos];
			}

			return $tBrackets[0][1];
		}

		public static function vardump($uVariable) {
			$tVariable = $uVariable;
			$tType = gettype($tVariable);
			$tOut = '';

			switch($tType) {
			case 'boolean':
				$tOut .= '<b>boolean</b>(' . (($tVariable) ? 'true' : 'false') . ')<br />';
				break;
			case 'integer':
			case 'double':
			case 'string':
				$tOut .= '<b>' . $tType . '</b>(\'' . $tVariable . '\')<br />';
				break;
			case 'array':
			case 'object':
				if($tType == 'object') {
					$tType = get_class($tVariable);
					$tVariable = @get_object_vars($tVariable);
				}

				$tCount = count($tVariable);
				$tOut .= '<b>' . $tType . '</b>(' . $tCount . ')';

				if($tCount > 0) {
					$tOut .= ' {' . '<div style="padding: 0px 0px 0px 50px;">';

					foreach($tVariable as $tKey => &$tVal) {
						$tOut .= '[' . $tKey . '] ';
						$tOut .= self::vardump($tVal);
					}

					$tOut .= '</div>}';
				}

				$tOut .= '<br />';
				break;
			case 'resource':
				$tOut .= '<b>resource</b>(\'' . get_resource_type($tVariable) . '\')<br />';
				break;
			case 'NULL':
				$tOut .= '<b><i>null</i></b><br />';
				break;
			case 'unknown type':
			default:
				$tOut .= 'unknown';
				break;
			}

			return $tOut;
		}

		public static function generatePassword($uLength) {
			srand(microtime(true) * 1000000);

			static $aVowels = array('a', 'e', 'i', 'o', 'u');
			static $aCons = array('b', 'c', 'd', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'u', 'v', 'w', 'tr', 'cr', 'br', 'fr', 'th', 'dr', 'ch', 'ph', 'wr', 'st', 'sp', 'sw', 'pr', 'sl', 'cl');

			$tConsLen = count($aCons) - 1;
			$tVowelsLen = count($aVowels) - 1;
			for($tOutput = '', $tLen = strlen($tOutput);$tLen < $uLength;) {
				$tOutput .= $aCons[rand(0, $tConsLen)] . $aVowels[rand(0, $tVowelsLen)];
			}

			// prevent overflow of size
			return substr($tOutput, 0, $uLength);
		}

		public static function generateUuid() {
			// return md5(uniqid(mt_rand(), true));
			return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				// 32 bits for "time_low"
				mt_rand(0, 0xffff),
				mt_rand(0, 0xffff),

				// 16 bits for "time_mid"
				mt_rand(0, 0xffff),

				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				mt_rand(0, 0x0fff) | 0x4000,

				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				mt_rand(0, 0x3fff) | 0x8000,

				// 48 bits for "node"
				mt_rand(0, 0xffff),
				mt_rand(0, 0xffff),
				mt_rand(0, 0xffff)
			);
		}

		public static function generate($uLength, $uCharset = '0123456789ABCDEF') {
			srand(microtime(true) * 1000000);

			$tCharsetLen = strlen($uCharset) - 1;
			for($tOutput = '', $tLen = strlen($tOutput);$tLen < $uLength;) {
				$tOutput .= $uCharset[rand(0, $tCharsetLen)];
			}

			return $tOutput;
		}

		public static function encrypt($uString, $uKey) {
			$tResult = '';

			for($i = 1, $tCount = strlen($uString); $i <= $tCount; $i++) {
				$tChar = substr($uString, $i - 1, 1);
				$tKeyChar = substr($uKey, ($i % strlen($uKey)) - 1, 1);
				$tResult .= chr(ord($tChar) + ord($tKeyChar));
			}

			return $tResult;
		}

		public static function decrypt($uString, $uKey) {
			$tResult = '';

			for($i = 1, $tCount = strlen($uString); $i <= $tCount; $i++) {
				$tChar = substr($uString, $i - 1, 1);
				$tKeyChar = substr($uKey, ($i % strlen($uKey)) - 1, 1);
				$tResult .= chr(ord($tChar) - ord($tKeyChar));
			}

			return $tResult;
		}

		public static function strip($uString, $uValids) {
			$tOutput = '';

			for($tCount = 0, $tLen = strlen($uString);$tCount < $tLen;$tCount++) {
				if(strpos($uValids, $uString[$tCount]) === false) {
					continue;
				}

				$tOutput .= $uString[$tCount];
			}

			return $tOutput;
		}

		public static function normalize($uString) {
			static $sTable = array(
				 'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A',
				 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I',
				 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O',
				 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a',
				 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
				 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o',
				 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y',
				 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f', 'Ş'=>'S', 'ş'=>'s', 'İ'=>'I', 'ı'=>'i', 'Ğ'=>'G',
				 'ğ'=>'g', 'ü'=>'u'
			);

			return strtr($uString, $sTable);
		}

		public static function squote($uString) {
			return strtr($uString, array('\\' => '\\\\', '\'' => '\\\''));
		}

		public static function dquote($uString) {
			return strtr($uString, array('\\' => '\\\\', '"' => '\\"'));
		}

		public static function replaceBreaks($uString, $uBreaks = '<br />') {
			return strtr($uString, array("\r" => '', "\n" => $uBreaks));
		}

		public static function cropText($uString, $uLength, $uContSign = '') {
			if(strlen($uString) <= $uLength) {
				return $uString;
			}

			return rtrim(substr($uString, 0, $uLength)) . $uContSign;
		}

		public static function encodeHtml($uString) {
			return strtr($uString, array('&' => '&amp;', '"' => '&quot;', '<' => '&lt;', '>' => '&gt;'));
		}

		public static function decodeHtml($uString) {
			return strtr($uString, array('&amp;' => '&', '&quot;' => '"', '&lt;' => '<', '&gt;' => '>'));
		}

		public static function toLower($uString) {
			return strtolower($uString);
		}

		public static function toUpper($uString) {
			return strtoupper($uString);
		}

		public static function sizeCalc($uSize, $uPrecision = 0) {
			static $tSize = ' KMGT';
			for($tCount = 0; $uSize >= 1024; $uSize /= 1024, $tCount++);

			return round($uSize, $uPrecision) . ' ' . $tSize[$tCount] . 'B';
		}

		public static function htmlHighlight($uString, $uKeyword) {
			if($uKeyword == '') {
				return $uString;
			}

			$tPosition = strpos(self::toLower($uString), self::toLower($uKeyword));

			if($tPosition === false) {
				return $uString;
			}

			return
				substr($uString, 0, $tPosition) .
				'<span style="background-color: yellow;">' .
				substr($uString, $tPosition, strlen($uKeyword)) .
				'</span>' .
				substr($uString, $tPosition + strlen($uKeyword))
			;
		}

		private static function readset_gquote($uString, &$uPosition) {
			$tInSlash = false;
			$tInQuote = false;
			$tOutput = '';

			for($tLen = strlen($uString);$uPosition <= $tLen;++$uPosition) {
				if(($uString[$uPosition] == '\\') && !$tInSlash) {
					$tInSlash = true;
					continue;
				}

				if($uString[$uPosition] == '"') {
					if(!$tInQuote) {
						$tInQuote = true;
						continue;
					}

					if(!$tInSlash) {
						return $tOutput;
					}
				}
				$tOutput .= $uString[$uPosition];
				$tInSlash = false;
			}

			return $tOutput;
		}

		public static function readset($uString) {
			$tStart = strpos($uString, '[');
			$tOutput = array();
			$tBuffer = '';

			if($tStart === false) {
				return $tOutput;
			}

			for($tLen = strlen($uString);$tStart <= $tLen;++$tStart) {
				if($uString[$tStart] == ']') {
					$tOutput[] = $tBuffer;
					$tBuffer = '';
					return $tOutput;
				}

				if($uString[$tStart] == ',') {
					$tOutput[] = $tBuffer;
					$tBuffer = '';
					continue;
				}

				if($uString[$tStart] == '"') {
					$tBuffer = self::readset_gquote($uString, $tStart);
					continue;
				}
			}

			return $tOutput;
		}

		public static function parseQueryString($uString, $uParameters = '?&', $uKeys = '=', $uSeperator = null) {
			$tParsed = array();
			$tStrings = array('', '');
			$tStrIndex = 0;

			$tPos = 0;
			$tLen = strlen($uString);

			if(!is_null($uSeperator)) {
				for(;$tPos < $tLen;$tPos++) {
					if(strpos($uSeperator, $uString[$tPos]) !== false) {
						if(strlen($tStrings[1]) > 0) {
							$tParsed[] = $tStrings[1];
						}

						$tStrings = array('', '');
						continue;
					}

					if(strpos($uParameters, $uString[$tPos]) !== false) {
						break;
					}

					$tStrings[1] .= $uString[$tPos];
				}
			}

			if(strlen($tStrings[1]) > 0) {
				if(strlen($tStrings[1]) > 0) {
					$tParsed[] = $tStrings[1];
				}

				$tStrings = array('', '');
			}

			for(;$tPos < $tLen;$tPos++) {
				if(strpos($uParameters, $uString[$tPos]) !== false) {
					if(strlen($tStrings[0]) > 0) {
						$tParsed[$tStrings[0]] = $tStrings[1];
						$tStrIndex = 0;
					}

					$tStrings = array('', '');
					continue;
				}

				if(strpos($uKeys, $uString[$tPos]) !== false && $tStrIndex < 1) {
					$tStrIndex++;
					continue;
				}

				$tStrings[$tStrIndex] .= $uString[$tPos];
			}

			if(strlen($tStrings[0]) > 0) {
				if(strlen($tStrings[0]) > 0) {
					$tParsed[$tStrings[0]] = $tStrings[1];
					$tStrIndex = 0;
				}

				$tStrings = array('', '');
			}

			return $tParsed;
		}

		public static function removeAccent($uString) {
			$tAccented = array('À','Á','Â','Ã','Ä','Å','Æ','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ð','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','ß','à','á','â','ã','ä','å','æ','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ','Ā','ā','Ă','ă','Ą','ą','Ć','ć','Ĉ','ĉ','Ċ','ċ','Č','č','Ď','ď','Đ','đ','Ē','ē','Ĕ','ĕ','Ė','ė','Ę','ę','Ě','ě','Ĝ','ĝ','Ğ','ğ','Ġ','ġ','Ģ','ģ','Ĥ','ĥ','Ħ','ħ','Ĩ','ĩ','Ī','ī','Ĭ','ĭ','Į','į','İ','ı','Ĳ','ĳ','Ĵ','ĵ','Ķ','ķ','Ĺ','ĺ','Ļ','ļ','Ľ','ľ','Ŀ','ŀ','Ł','ł','Ń','ń','Ņ','ņ','Ň','ň','ŉ','Ō','ō','Ŏ','ŏ','Ő','ő','Œ','œ','Ŕ','ŕ','Ŗ','ŗ','Ř','ř','Ś','ś','Ŝ','ŝ','Ş','ş','Š','š','Ţ','ţ','Ť','ť','Ŧ','ŧ','Ũ','ũ','Ū','ū','Ŭ','ŭ','Ů','ů','Ű','ű','Ų','ų','Ŵ','ŵ','Ŷ','ŷ','Ÿ','Ź','ź','Ż','ż','Ž','ž','ſ','ƒ','Ơ','ơ','Ư','ư','Ǎ','ǎ','Ǐ','ǐ','Ǒ','ǒ','Ǔ','ǔ','Ǖ','ǖ','Ǘ','ǘ','Ǚ','ǚ','Ǜ','ǜ','Ǻ','ǻ','Ǽ','ǽ','Ǿ','ǿ');
			$tStraight = array('A','A','A','A','A','A','AE','C','E','E','E','E','I','I','I','I','D','N','O','O','O','O','O','O','U','U','U','U','Y','s','a','a','a','a','a','a','ae','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y','A','a','A','a','A','a','C','c','C','c','C','c','C','c','D','d','D','d','E','e','E','e','E','e','E','e','E','e','G','g','G','g','G','g','G','g','H','h','H','h','I','i','I','i','I','i','I','i','I','i','IJ','ij','J','j','K','k','L','l','L','l','L','l','L','l','l','l','N','n','N','n','N','n','n','O','o','O','o','O','o','OE','oe','R','r','R','r','R','r','S','s','S','s','S','s','S','s','T','t','T','t','T','t','U','u','U','u','U','u','U','u','U','u','U','u','W','w','Y','y','Y','Z','z','Z','z','Z','z','s','f','O','o','U','u','A','a','I','i','O','o','U','u','U','u','U','u','U','u','U','u','A','a','AE','ae','O','o');

			return str_replace($tAccented, $tStraight, $uString);
		}

		public static function slug($uString) {
			$uString = strtolower(trim($uString));
			$uString = preg_replace('/[^a-z0-9-]/', '_', $uString);
			$uString = preg_replace('/-+/', '_', $uString);

			return $uString;
		}
	}
}

?>