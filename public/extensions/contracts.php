<?php

if(Extensions::isSelected('contracts')) {
	class contracts {
		const IsNumeric = 1;
		const IsEqual = 2;
		const IsMinimum = 3;
		const IsMinimumOrEqual = 4;
		const IsMaximum = 5;
		const IsMaximumOrEqual = 6;
		const Length = 7;
		const LengthMinimum = 8;
		const LengthMaximum = 9;
		const RegExp = 10;
		const Custom = 0;

		public static function extension_info() {
			return array(
				'name' => 'contracts',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		public static function check() {
			$uRule = func_get_args();
			$uController = array_shift($uRule);
			$uValue = array_shift($uRule);

			if(count($uRule) == 0) {
				if(!$uValue) {
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				return true;
			}

			switch($uRule[0]) {
			case self::IsNumeric:
				if(!is_numeric($uRule[1])) {
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				break;
			case self::IsEqual:
				for($tCount = count($uRule) - 1;$tCount > 0;$tCount--) {
					if($uValue == $uRule[$tCount]) {
						$tPasses = true;
						break;
					}
				}
				
				if(!isset($tPasses)) {
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				break;
			case self::IsMinimum:
				if($uValue >= $uRule[1]) { // inverse of <
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				break;
			case self::IsMinimumOrEqual:
				if($uValue > $uRule[1]) { // inverse of <=
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				break;
			case self::IsMaximum:
				if($uValue <= $uRule[1]) { // inverse of >
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				break;
			case self::IsMaximumOrEqual:
				if($uValue < $uRule[1]) { // inverse of >=
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				break;
			case self::Length:
				if(strlen($uValue) != $uRule[1]) {
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				break;
			case self::LengthMinimum:
				if(strlen($uValue) < $uRule[1]) {
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				break;
			case self::LengthMaximum:
				if(strlen($uValue) > $uRule[1]) {
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				break;
			case self::RegExp:
				if(!preg_match($uRule[1], $uValue)) {
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				break;
			case self::Custom:
				if(!call_user_func($uRule[1], $uValue)) {
					if(is_null($uController)) {
						// throw new Exception('Condition fail');
						return false;
					}

					$uController->error('Condition fail');
					return false;
				}

				break;
			}

			return true;
		}
	}
}

?>