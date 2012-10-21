<?php

if(extensions::isSelected('contracts')) {
	/**
	* Contracts Extension
	*
	* @package Scabbia
	* @subpackage contracts
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*
	* @todo add more validators such as phone, hex, octal, digit, isUnique, etc.
	*/
	class contracts {
		/**
		* @ignore
		*/
		const isExist = 0;
		/**
		* @ignore
		*/
		const isRequired = 1;
		/**
		* @ignore
		*/
		const isBoolean = 2;
		/**
		* @ignore
		*/
		const isFloat = 3;
		/**
		* @ignore
		*/
		const isInteger = 4;
		/**
		* @ignore
		*/
		const isHex = 5;
		/**
		* @ignore
		*/
		const isOctal = 6;
		/**
		* @ignore
		*/
		const isNumeric = 7;
		/**
		* @ignore
		*/
		const isSlugString = 8;
		/**
		* @ignore
		*/
		const isDate = 9;
		/**
		* @ignore
		*/
		const isUuid = 10;
		/**
		* @ignore
		*/
		const isEmail = 11;
		/**
		* @ignore
		*/
		const isUrl = 12;
		/**
		* @ignore
		*/
		const isIpAddress = 13;
		/**
		* @ignore
		*/
		const isEqual = 14;
		/**
		* @ignore
		*/
		const isMinimum = 15;
		/**
		* @ignore
		*/
		const isLower = 16;
		/**
		* @ignore
		*/
		const isMaximum = 17;
		/**
		* @ignore
		*/
		const isGreater = 18;
		/**
		* @ignore
		*/
		const length = 19;
		/**
		* @ignore
		*/
		const lengthMinimum = 20;
		/**
		* @ignore
		*/
		const lengthMaximum = 21;
		/**
		* @ignore
		*/
		const inArray = 22;
		/**
		* @ignore
		*/
		const regExp = 23;
		/**
		* @ignore
		*/
		const custom = 24;

		/**
		* @ignore
		*/
		const pregEmail = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
		/**
		* @ignore
		*/
		const pregUrl = '/^(https?|ftp):\/\/((?:[a-z0-9@:.-]|%[0-9A-F]{2}){3,})(?::(\d+))?((?:\/(?:[a-z0-9-._~!$&\'()*+,;=:@]|%[0-9A-F]{2})*)*)(?:\?((?:[a-z0-9-._~!$&\'()*+,;=:\/?@]|%[0-9A-F]{2})*))?(?:#((?:[a-z0-9-._~!$&\'()*+,;=:\/?@]|%[0-9A-F]{2})*))?/i';
		/**
		* @ignore
		*/
		const pregIpAddress = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/';

		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'contracts',
				'version' => '1.0.2',
				'phpversion' => '5.2.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		/**
		* @ignore
		*/
		public static function test($uType, $uValue, $uArgs) {
			switch($uType) {
			case self::isRequired:
				if(strlen(chop($uValue)) == 0) {
					return false;
				}

				break;
			case self::isBoolean:
				if($uValue !== false && $uValue !== true &&
					$uValue != 'false' && $uValue != 'true' &&
					$uValue !== 0 && $uValue !== 1 &&
					$uValue != '0' && $uValue != '1'
					) {
					return false;
				}

				break;
			case self::isFloat:
				if(filter_var($uValue, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND) === false) {
					return false;
				}

				break;
			case self::isInteger:
				if(filter_var($uValue, FILTER_VALIDATE_INT) === false) {
					return false;
				}

				break;
			case self::isHex:
				if(filter_var($uValue, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_HEX) === false) {
					return false;
				}

				break;
			case self::isOctal:
				if(filter_var($uValue, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_OCTAL) === false) {
					return false;
				}

				break;
			case self::isNumeric:
				if(ctype_digit($uValue) === false) {
					return false;
				}

				break;
			case self::isSlugString:
				for($i = mb_strlen($uValue) - 1;$i >= 0;$i--) {
					$tChar = mb_substr($uValue, $i, 1);

					if(!ctype_alnum($uValue[$i]) && $uValue[$i] != '-') {
						return false;
					}
				}

				break;
			case self::isDate:
				$tArray = date_parse_from_format($uArgs[0], $uValue);
				if($tArray['error_count'] > 0) {
					return false;
				}

				if(!checkdate($tArray['month'], $tArray['day'], $tArray['year'])) {
					return false;
				}

				break;
			case self::isUuid:
				if(strlen($uValue) != 36) {
					return false;
				}

				for($i = strlen($uValue) - 1;$i >= 0;$i--) {
					if($i == 8 || $i == 13 || $i == 18 || $i == 23) {
						if($uValue[$i] != '-') {
							return false;
						}

						continue;
					}

					if(!ctype_xdigit($uValue[$i])) {
						return false;
					}
				}

				break;
			case self::isEqual:
				for($tCount = count($uArgs) - 1;$tCount >= 0;$tCount--) {
					if($uValue == $uArgs[$tCount]) {
						$tPasses = true;
						break;
					}
				}

				if(!isset($tPasses)) {
					return false;
				}

				break;
			case self::isMinimum:
				if($uValue < $uArgs[0]) {
					return false;
				}

				break;
			case self::isLower:
				if($uValue >= $uArgs[0]) {
					return false;
				}

				break;
			case self::isMaximum:
				if($uValue > $uArgs[0]) {
					return false;
				}

				break;
			case self::isGreater:
				if($uValue <= $uArgs[0]) {
					return false;
				}

				break;
			case self::length:
				if(strlen($uValue) != $uArgs[0]) {
					return false;
				}

				break;
			case self::lengthMinimum:
				if(strlen($uValue) < $uArgs[0]) { // inverse of >=
					return false;
				}

				break;
			case self::lengthMaximum:
				if(strlen($uValue) > $uArgs[0]) {  // inverse of <=
					return false;
				}

				break;
			case self::inArray:
				if(!in_array($uArgs, $uValue)) {
					return false;
				}

				break;
			case self::regExp:
				if(!preg_match($uArgs[0], $uValue)) {
					return false;
				}

				break;
			case self::custom:
				if(!call_user_func($uArgs[0], $uValue)) {
					return false;
				}

				break;
			case self::isEmail:
				// if(!preg_match(self::pregEmail, $uValue)) {
				if(filter_var($uValue, FILTER_VALIDATE_EMAIL) === false) {
					return false;
				}

				break;
			case self::isUrl:
				if(filter_var($uValue, FILTER_VALIDATE_URL) === false) {
					return false;
				}

				break;
			case self::isIpAddress:
				if(filter_var($uValue, FILTER_VALIDATE_IP) === false) {
					return false;
				}

				break;
			}

			return true;
		}

		/**
		* @ignore
		*/
		public static function __callStatic($uName, $uArgs) {
			$tContractObject = new contractObject(
				array_shift($uArgs),
				constant('contracts::' . $uName),
				$uArgs
			);

			return $tContractObject;
		}

		/**
		* @ignore
		*/
		public static function check() {
			$uArgs = func_get_args();
			$uName = array_shift($uArgs);
			$uValue = array_shift($uArgs);

			$tContractObject = new contractObject(
				$uValue,
				constant('contracts::' . $uName),
				$uArgs
			);

			return $tContractObject->check();
		}
	}

	/**
	* Contract Object Class
	*
	* @package Scabbia
	* @subpackage ExtensibilityExtensions
	*/
	class contractObject {
		/**
		* @ignore
		*/
		public $value;
		/**
		* @ignore
		*/
		public $type;
		/**
		* @ignore
		*/
		public $args;

		/**
		* @ignore
		*/
		public function __construct($uValue, $uType, $uArgs) {
			$this->value = $uValue;
			$this->type = $uType;
			$this->args = $uArgs;
		}

		/**
		* @ignore
		*/
		public function error(&$uController, $uErrorMessage) {
			if(contracts::test($this->type, $this->value, $this->args)) {
				return;
			}

			$uController->error($uErrorMessage);
		}

		/**
		* @ignore
		*/
		public function exception($uErrorMessage) {
			if(contracts::test($this->type, $this->value, $this->args)) {
				return;
			}

			throw new Exception($uErrorMessage);
		}

		/**
		* @ignore
		*/
		public function check() {
			if(contracts::test($this->type, $this->value, $this->args)) {
				return true;
			}

			return false;
		}
	}
}

?>