<?php

if(Extensions::isSelected('contracts')) {
	class contracts {
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

		public static function check($uCondition) {
			if(!$uCondition) {
				throw new Exception('Condition fail');
			}
		}
	}
}

?>