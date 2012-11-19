<?php

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
		public static function isRequired($uValue) {
			if(strlen(chop($uValue)) == 0) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isBoolean($uValue) {
			if($uValue !== false && $uValue !== true &&
				$uValue != 'false' && $uValue != 'true' &&
				$uValue !== 0 && $uValue !== 1 &&
				$uValue != '0' && $uValue != '1'
			) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isFloat($uValue) {
			if(filter_var($uValue, FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_THOUSAND) === false) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isInteger($uValue) {
			if(filter_var($uValue, FILTER_VALIDATE_INT) === false) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isHex($uValue) {
			if(filter_var($uValue, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_HEX) === false) {
				return new contractObject(false);
			}
			
			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isOctal($uValue) {
			if(filter_var($uValue, FILTER_VALIDATE_INT, FILTER_FLAG_ALLOW_OCTAL) === false) {
				return new contractObject(false);
			}
			
			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isNumeric($uValue) {
			if(ctype_digit($uValue) === false) {
				return new contractObject(false);
			}
			
			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isSlugString($uValue) {
			for($i = mb_strlen($uValue) - 1; $i >= 0; $i--) {
				$tChar = mb_substr($uValue, $i, 1);

				if(!ctype_alnum($tChar) && $tChar != '-') {
					return new contractObject(false);
				}
			}
			
			return new contractObject(true);
		}
		
		/**
		 * @ignore
		 * PHP 5.3 only.
		 */
		public static function isDate($uValue, $uFormat) {
			$tArray = date_parse_from_format($uFormat, $uValue);
			if($tArray['error_count'] > 0) {
				return new contractObject(false);
			}

			if(!checkdate($tArray['month'], $tArray['day'], $tArray['year'])) {
				return new contractObject(false);
			}
			
			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isUuid($uValue) {
			if(strlen($uValue) != 36) {
				return new contractObject(false);
			}

			for($i = strlen($uValue) - 1; $i >= 0; $i--) {
				if($i == 8 || $i == 13 || $i == 18 || $i == 23) {
					if($uValue[$i] != '-') {
						return new contractObject(false);
					}

					continue;
				}

				if(!ctype_xdigit($uValue[$i])) {
					return new contractObject(false);
				}
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isEqual() {
			$uArgs = func_get_args();
			$uValue = array_shift($uArgs);

			for($tCount = count($uArgs) - 1; $tCount >= 0; $tCount--) {
				if($uValue == $uArgs[$tCount]) {
					$tPasses = true;
					break;
				}
			}

			if(!isset($tPasses)) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isMinimum($uValue, $uOtherValue) {
			if($uValue < $uOtherValue) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isLower($uValue, $uOtherValue) {
			if($uValue >= $uOtherValue) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isMaximum($uValue, $uOtherValue) {
			if($uValue > $uOtherValue) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isGreater($uValue, $uOtherValue) {
			if($uValue <= $uOtherValue) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}
		
		/**
		 * @ignore
		 */
		public static function length($uValue, $uOtherValue) {
			if(strlen($uValue) != $uOtherValue) {
				return new contractObject(false);
			}
			
			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function lengthMinimum($uValue, $uOtherValue) {
			if(strlen($uValue) < $uOtherValue) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}
		
		/**
		 * @ignore
		 */
		public static function lengthMaximum($uValue, $uOtherValue) {
			if(strlen($uValue) > $uOtherValue) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function inArray($uValue, $uArray) {
			if(!in_array($uValue, $uArray)) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function regExp($uValue, $uExpression) {
			if(!preg_match($uExpression, $uValue)) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function custom($uValue, $uFunction) {
			if(!call_user_func($uFunction, $uValue)) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isEmail($uValue) {
			// if(!preg_match(self::pregEmail, $uValue)) {
			if(filter_var($uValue, FILTER_VALIDATE_EMAIL) === false) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isUrl($uValue) {
			if(filter_var($uValue, FILTER_VALIDATE_URL) === false) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isIpAddress($uValue) {
			if(filter_var($uValue, FILTER_VALIDATE_IP) === false) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function getEmail($uValue) {
			// if(filter_var($uValue, FILTER_VALIDATE_EMAIL) === false) {
			//	return new contractObject(false);
			// }

			$tParts = explode('@', $uValue);
			if(count($tPart) != 2) {
				return new contractObject(false);
			}

			$tPart[1] = strtolower($tPart[1]);
			switch($tPart[1]) {
			case 'gmail.com':
			case 'googlemail.com':
				$tPlusEnabled = true;
				break;
			}

			if(isset($tPlusEnabled)) {
				// strpos('+', $tPart[0]);
			}

			return new contractObject(true, $tPart[0] . '@' . $tPart[1]);
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
		public $status;
		/**
		 * @ignore
		 */
		public $newValue;

		/**
		 * @ignore
		 */
		public function __construct($uStatus, $uNewValue = null) {
			$this->status = $uStatus;
			$this->newValue = $uNewValue;
		}

		/**
		 * @ignore
		 */
		public function error(&$uController, $uErrorMessage) {
			if(!$this->status) {
				return;
			}

			$uController->error($uErrorMessage);
		}

		/**
		 * @ignore
		 */
		public function exception($uErrorMessage) {
			if(!$this->status) {
				return;
			}

			throw new Exception($uErrorMessage);
		}

		/**
		 * @ignore
		 */
		public function &check() {
			return $this->status;
		}

		/**
		 * @ignore
		 */
		public function get() {
			if(!$this->status) {
				return false;
			}

			return $this->newValue;
		}
	}

?>