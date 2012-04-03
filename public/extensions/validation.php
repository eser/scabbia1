<?php

if(Extensions::isSelected('validation')) {
	class validation {
		public static $rules = array();
		public static $summary = null;

		const IsNumeric = 1;
		const IsEqual = 2;
		const IsLower = 3;
		const IsLowerEq = 4;
		const IsGreater = 5;
		const IsGreaterEq = 6;
		const Length = 7;
		const TrimmedLength = 8;
		const RegExp = 9;
		const Custom = 10;

		public static function extension_info() {
			return array(
				'name' => 'validation',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string')
			);
		}

		public static function addRule() {
			$uArgs = func_get_args();
			$uKey = array_shift($uArgs);

			if(!array_key_exists($uKey, self::$rules)) {
				self::$rules[$uKey] = array(
					$uArgs
				);

				return;
			}

			self::$rules[$uKey][] = $uArgs;
		}

		public static function clear() {
			self::$rules = array();
			self::$summary = null;
		}
		
		public static function validate($uArray) {
			self::$summary = array();

			foreach($uArray as $tKey => &$tValue) {
				if(!array_key_exists($tKey, self::$rules)) {
					self::$summary[] = $tRule;
					continue;
				}

				foreach(self::$rules[$tKey] as &$tRule) {
					switch($tRule[0]) {
					case self::IsNumeric:
						if(!is_numeric($tRule[1])) {
							self::$summary[] = $tRule;
						}

						break;
					case self::IsEqual:
						for($tCount = count($tRule) - 1;$tCount > 0;$tCount--) {
							if($tValue == $tRule[$tCount]) {
								$tPasses = true;
								break;
							}
						}
						
						if(!isset($tPasses)) {
							self::$summary[] = $tRule;
						}

						break;
					case self::IsLower:
						if($tValue >= $tRule[1]) { // inverse of <
							self::$summary[] = $tRule;
						}

						break;
					case self::IsLowerEq:
						if($tValue > $tRule[1]) { // inverse of <=
							self::$summary[] = $tRule;
						}

						break;
					case self::IsGreater:
						if($tValue <= $tRule[1]) { // inverse of >
							self::$summary[] = $tRule;
						}

						break;
					case self::IsGreaterEq:
						if($tValue < $tRule[1]) { // inverse of >=
							self::$summary[] = $tRule;
						}

						break;
					case self::Length:
						if(strlen($tValue) < $tRule[1]) {
							self::$summary[] = $tRule;
						}

						break;
					case self::TrimmedLength:
						if(strlen(trim($tValue)) < $tRule[1]) {
							self::$summary[] = $tRule;
						}

						break;
					case self::RegExp:
						if(!preg_match($tRule[1], $tValue)) {
							self::$summary[] = $tRule;
						}

						break;
					case self::Custom:
						if(!call_user_func($tRule[1], $tValue)) {
							self::$summary[] = $tRule;
						}

						break;
					}
				}
			}

			return (count(self::$summary) == 0);
		}

		public static function export() {
			return string::vardump(self::$summary);
		}
	}
}

?>