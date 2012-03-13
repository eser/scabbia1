<?php

if(Extensions::isSelected('unittest')) {
	class unittest {
		private static $stack = array();
		private static $report = array();

		public static function extension_info() {
			return array(
				'name' => 'unittest',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string')
			);
		}

		public static function beginClass($uClass) {
			$tMethods = get_class_methods($uClass);

			$tInstance = new $uClass ();
			foreach($tMethods as &$tMethod) {
				self::begin($uClass . '->' . $tMethod . '()', array(&$tInstance, $tMethod));
			}
		}

		public static function begin($uName, $uCallback) {
			array_push(self::$stack, array('name' => $uName, 'callback' => $uCallback));
			call_user_func($uCallback);
			array_pop(self::$stack);
		}
		
		private static function addReport($uOperation, $uIsFailed) {
			$tScope = end(self::$stack);

			if(!array_key_exists($tScope['name'], self::$report)) {
				self::$report[$tScope['name']] = array();
			}

			self::$report[$tScope['name']][] = array(
				'operation' => $uOperation,
				'failed' => $uIsFailed
			);
		}

		public static function assertTrue($uCondition) {
			if($uCondition) {
				self::addReport('assertTrue', true);
				return;
			}

			self::addReport('assertTrue', false);
		}

		public static function assertFalse($uCondition) {
			if(!$uCondition) {
				self::addReport('assertFalse', true);
				return;
			}

			self::addReport('assertFalse', false);
		}

		public static function assertNull($uVariable) {
			if(is_null($uVariable)) {
				self::addReport('assertNull', true);
				return;
			}

			self::addReport('assertNull', false);
		}

		public static function assertNotNull($uVariable) {
			if(!is_null($uVariable)) {
				self::addReport('assertNotNull', true);
				return;
			}

			self::addReport('assertNotNull', false);
		}

		public static function getlist() {
			return self::$report;
		}

		public static function export() {
			return string::vardump(self::$report);
		}
	}
}

?>
