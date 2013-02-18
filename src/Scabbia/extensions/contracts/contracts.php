<?php

	namespace Scabbia\Extensions\Contracts;

	use Scabbia\Extensions\Contracts\contractObject;

	/**
	 * Contracts Extension
	 *
	 * @package Scabbia
	 * @subpackage contracts
	 * @version 1.0.5
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
		const REGEXP_EMAIL = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
		/**
		 * @ignore
		 */
		const REGEXP_URL = '/^(https?|ftp):\/\/((?:[a-z0-9@:.-]|%[0-9A-F]{2}){3,})(?::(\d+))?((?:\/(?:[a-z0-9-._~!$&\'()*+,;=:@]|%[0-9A-F]{2})*)*)(?:\?((?:[a-z0-9-._~!$&\'()*+,;=:\/?@]|%[0-9A-F]{2})*))?(?:#((?:[a-z0-9-._~!$&\'()*+,;=:\/?@]|%[0-9A-F]{2})*))?/i';
		/**
		 * @ignore
		 */
		const REGEXP_IPV4 = '/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/';

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
		public static function inKeys($uKey, $uArray) {
			if(!array_key_exists($uKey, $uArray)) {
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
		public static function isNotFalse($uValue) {
			if($uValue === false) {
				return new contractObject(false);
			}

			return new contractObject(true);
		}

		/**
		 * @ignore
		 */
		public static function isEmail($uValue) {
			// if(!preg_match(self::REGEXP_EMAIL, $uValue)) {
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

			$uValue = strtr($uValue, 'ABCDEFGHIJKLMNOPRQSTUVWXYZ', 'abcdefghijklmnoprqstuvwxyz');

			$tValidated = array('', '');
			$tIndex = 1;
			for($i = strlen($uValue) - 1; $i >= 0; $i--) {
				if($uValue[$i] == '@') {
					if(--$tIndex <= 0) {
						continue;
					}

					// direct termination
					return new contractObject(false);
				}

				if(strpos('abcdefghijklmnoprqstuvwxyz0123456789.+-_', $uValue[$i]) !== false) {
					$tValidated[$tIndex] = $uValue[$i] . $tValidated[$tIndex];
				}
			}

			if($tIndex > 0) {
				return new contractObject(false);
			}

			return new contractObject(true, $tValidated[0] . '@' . $tValidated[1]);
		}
	}

	?>