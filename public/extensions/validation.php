<?php

if(extensions::isSelected('validation')) {
	class validation {
		public static $rules = array();
		public static $summary = null;

		public static function extension_info() {
			return array(
				'name' => 'validation',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string', 'contracts')
			);
		}

		public static function addRule($uKey) {
			$tRule = new validationRule($uKey);
			self::$rules[] = $tRule;

			return $tRule;
		}

		public static function clear() {
			self::$rules = array();
			self::$summary = null;
		}

		private static function addSummary($uRule) {
			if(is_null(self::$summary)) {
				self::$summary = array();
			}
			
			if(!isset(self::$summary[$uRule->field])) {
				self::$summary[$uRule->field] = array();
			}

			self::$summary[$uRule->field][] = $uRule;
		}

		public static function validate($uArray) {
			foreach(self::$rules as &$tRule) {
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

			return (count(self::$summary) == 0);
		}

		public static function hasErrors() {
			$uArgs = func_get_args();

			if(count($uArgs) > 0) {
				return array_key_exists($uArgs[0], self::$summary);
			}

			return (count(self::$summary) > 0);
		}

		public static function getErrors($uKey) {
			if(!array_key_exists($uKey, self::$summary)) {
				return false;
			}

			return self::$summary($uKey);
		}

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

		public static function export() {
			return string::vardump(self::$summary);
		}
	}

	class validationRule {
		public $field;
		public $type;
		public $args;
		public $errorMessage;

		public function __construct($uField) {
			$this->field = $uField;
		}

		public function __call($uName, $uArgs) {
			$this->type = constant('contracts::' . $uName);
			$this->args = $uArgs;

			return $this;
		}

		public function errorMessage($uErrorMessage) {
			$this->errorMessage = $uErrorMessage;
		}
	}
}

?>