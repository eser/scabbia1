<?php

if(extensions::isSelected('contracts')) {
	/**
	* Contracts Extension
	*
	* @package Scabbia
	* @subpackage ExtensibilityExtensions
	*
	* @todo add more validators such as phone, date, digit, isUnique, etc.
	*/
	class contracts {
		const isExist = 0;
		const isRequired = 1;
		const isNumeric = 2;
		const isEqual = 3;
		const isMinimum = 4;
		const isMinimumOrEqual = 5;
		const isMaximum = 6;
		const isMaximumOrEqual = 7;
		const length = 8;
		const lengthMinimum = 9;
		const lengthMaximum = 10;
		const inArray = 11;
		const regExp = 12;
		const custom = 13;
		const isEmail = 14;
		const isUrl = 15;
		const isIpAddress = 16;

		const pregEmail = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
		const pregUrl = '/^(https?|ftp):\/\/((?:[a-z0-9@:.-]|%[0-9A-F]{2}){3,})(?::(\d+))?((?:\/(?:[a-z0-9-._~!$&\'()*+,;=:@]|%[0-9A-F]{2})*)*)(?:\?((?:[a-z0-9-._~!$&\'()*+,;=:\/?@]|%[0-9A-F]{2})*))?(?:#((?:[a-z0-9-._~!$&\'()*+,;=:\/?@]|%[0-9A-F]{2})*))?/i';
		const pregIpAddress = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/';

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

		public static function test($uType, $uValue, $uArgs) {
			switch($uType) {
			case self::isRequired:
				if(strlen(chop($uValue)) == 0) {
					return false;
				}

				break;
			case self::isNumeric:
				if(!is_numeric($uValue)) {
					return false;
				}

				break;
			case self::isEqual:
				for($tCount = count($uArgs);$tCount > 0;$tCount--) {
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
				if($uValue >= $uArgs[0]) { // inverse of <
					return false;
				}

				break;
			case self::isMinimumOrEqual:
				if($uValue > $uArgs[0]) { // inverse of <=
					return false;
				}

				break;
			case self::isMaximum:
				if($uValue <= $uArgs[0]) { // inverse of >
					return false;
				}

				break;
			case self::isMaximumOrEqual:
				if($uValue < $uArgs[0]) { // inverse of >=
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

		public static function __callStatic($uName, $uArgs) {
			$tContractObject = new contractObject(
				array_shift($uArgs),
				constant('contracts::' . $uName),
				$uArgs
			);

			return $tContractObject;
		}
	}

	/**
	* Contract Object Class
	*
	* @package Scabbia
	* @subpackage ExtensibilityExtensions
	*/
	class contractObject {
		public $value;
		public $type;
		public $args;

		public function __construct($uValue, $uType, $uArgs) {
			$this->value = $uValue;
			$this->type = $uType;
			$this->args = $uArgs;
		}

		public function error(&$uController, $uErrorMessage) {
			if(contracts::test($this->type, $this->value, $this->args)) {
				return;
			}

			$uController->error($uErrorMessage);
		}

		public function exception($uErrorMessage) {
			if(contracts::test($this->type, $this->value, $this->args)) {
				return;
			}

			throw new Exception($uErrorMessage);
		}

		public function check() {
			if(contracts::test($this->type, $this->value, $this->args)) {
				return true;
			}

			return false;
		}
	}
}

?>