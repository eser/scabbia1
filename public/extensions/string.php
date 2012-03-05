<?php

	class string {
		public static function extension_info() {
			return array(
				'name' => 'string',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'fwversion' => '1.0',
				'enabled' => true,
				'autoevents' => false,
				'depends' => array()
			);
		}

		public static function filter($uVariable, $uFilter) {
			switch($uFilter) {
			case 'int':
			case 'integer':
				return intval($uVariable);
				break;
			case 'squote':
				return self::squote($uVariable);
				break;
			case 'dquote':
				return self::dquote($uVariable);
				break;
			case 'upper':
				return self::toUpper($uVariable);
				break;
			case 'lower':
				return self::toLower($uVariable);
				break;
			case 'num':
			case 'number':
				return number_format($uVariable);
				break;
			case 'html':
				return htmlspecialchars($uVariable);
				break;
			}
			
			return $uVariable;
		}
		
		public static function format($uString) {
			$tParms = func_get_args();
			array_shift($tParms);

			if(is_array($tParms[0])) {
				$tParms = $tParms[0];
			}

			$tBrackets = array('');
			$tLastItem = 0;

			for($tPos = 0, $tLen = strlen($uString);$tPos < $tLen;$tPos++) {
				if($uString[$tPos] == '\\') {
					$tBrackets[$tLastItem] .= $uString[++$tPos];
					continue;
				}

				$tLastItem = count($tBrackets) - 1;

				if($uString[$tPos] == '{') {
					$tBrackets[$tLastItem + 1] = '';
					continue;
				}

				if($uString[$tPos] == '}' && $tLastItem > 0) {
					$tExploded = explode(':', $tBrackets[$tLastItem]);
					unset($tBrackets[$tLastItem]);

					$tString = $tParms[$tExploded[count($tExploded) - 1]];

					for($i = 0, $tCount = count($tExploded) - 1;$i < $tCount;$i++) {
						$tString = self::filter($tString, $tExploded[$i]);
					}

					$tBrackets[$tLastItem - 1] .= $tString;
					continue;
				}

				$tBrackets[$tLastItem] .= $uString[$tPos];
			}

			return $tBrackets[0];
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

		public static function parseQueryString($uString, $uParameters = '&', $uKeys = '=') {
			$tParsed = array();

			foreach(explode($uParameters, $uString) as $tParameter) {
				$tParameters = explode($uKeys, trim($tParameter), 2);
				if($tParameters[0] == '') {
					continue;
				}

				$tParsed[$tParameters[0]] = (isset($tParameters[1])) ? $tParameters[1] : '';
			}

			return $tParsed;
		}
	}

?>