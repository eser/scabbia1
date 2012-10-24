<?php

	/**
	* Validation Extension
	*
	* @package Scabbia
	* @subpackage validation
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends contracts
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class validation {
		/**
		* @ignore
		*/
		public static $rules = array();
		/**
		* @ignore
		*/
		public static $summary = array();

		/**
		* @ignore
		*/
		public static function addRule($uKey = null) {
			$tRule = new validationRule($uKey);
			self::$rules[] = &$tRule;

			return $tRule;
		}

		/**
		* @ignore
		*/
		public static function clear() {
			self::$rules = array();
			self::$summary = array();
		}

		/**
		* @ignore
		*/
		private static function addSummary($uRule) {
			if(!isset(self::$summary[$uRule->field])) {
				self::$summary[$uRule->field] = array();
			}

			self::$summary[$uRule->field][] = $uRule;
		}

		/**
		* @ignore
		*/
		public static function validate($uArray = null) {
			foreach(self::$rules as &$tRule) {
				if(!is_null($tRule->value)) {
					if(!contracts::test($tRule->type, $tRule->value, $tRule->args)) {
						self::addSummary($tRule);
					}

					continue;
				}

				if(!is_null($uArray)) {
					if(!array_key_exists($tRule->field, $uArray)) {
						if($tRule->type == contracts::isExist) {
							self::addSummary($tRule);
						}

						continue;
					}

					if(!contracts::test($tRule->type, $uArray[$tRule->field], $tRule->args)) {
						self::addSummary($tRule);
					}
				}
			}

			return (count(self::$summary) == 0);
		}

		/**
		* @ignore
		*/
		public static function hasErrors() {
			$uArgs = func_get_args();

			if(count($uArgs) > 0) {
				return array_key_exists($uArgs[0], self::$summary);
			}

			return (count(self::$summary) > 0);
		}

		/**
		* @ignore
		*/
		public static function getErrors($uKey) {
			if(!array_key_exists($uKey, self::$summary)) {
				return false;
			}

			return self::$summary($uKey);
		}

		/**
		* @ignore
		*/
		public static function getErrorMessages($uFirsts = false, $uFilter = false) {
			$tMessages = array();

			foreach(self::$summary as $tKey => &$tField) {
				if($uFilter !== false && $uFilter != $tKey) {
					continue;
				}

				foreach($tField as &$tRule) {
					if(is_null($tRule->errorMessage)) {
						continue;
					}

					$tMessages[] = $tRule->errorMessage;
					if($uFirsts) {
						break;
					}
				}
			}

			return $tMessages;
		}

		/**
		* @ignore
		*/
		public static function export($tOutput = true) {
			if(extensions::isLoaded('string')) {
				return string::vardump(self::$summary, $tOutput);
			}

			return print_r(self::$summary, $tOutput);
		}
	}

	/**
	* Validation Rule Class
	*
	* @package Scabbia
	* @subpackage ExtensibilityExtensions
	*/
	class validationRule {
		/**
		* @ignore
		*/
		public $field;
		/**
		 * @ignore
		 */
		public $value = null;
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
		public $errorMessage;

		/**
		* @ignore
		*/
		public function __construct(&$uField) {
			$this->field = &$uField;
		}

		/**
		* @ignore
		*/
		public function __call($uName, $uArgs) {
			$this->type = constant('contracts::' . $uName);
			$this->args = &$uArgs;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &field($uField) {
			$this->field = &$uField;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &value($uValue) {
			$this->value = &$uValue;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function errorMessage($uErrorMessage) {
			$this->errorMessage = &$uErrorMessage;
		}
	}

?>