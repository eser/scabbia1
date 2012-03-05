<?php

	class contracts {
		public static function extension_info() {
			return array(
				'name' => 'contracts',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'fwversion' => '1.0',
				'enabled' => true,
				'autoevents' => false,
				'depends' => array()
			);
		}

		public static function check($uCondition) {
			if(!$uCondition) {
				throw new Exception('Condition fail');
			}
		}
	}

?>