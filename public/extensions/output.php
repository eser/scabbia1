<?php

if(extensions::isSelected('output')) {
	class output {
		public static $effectList = array();

		public static function extension_info() {
			return array(
				'name' => 'output',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		public static function extension_load() {
		}

		public static function begin() {
			ob_start('output::flushOutput');
			ob_implicit_flush(false);

			$tArgs = func_get_args();
			array_push(self::$effectList, $tArgs);
		}

		public static function &end($uFlush = true) {
			$tContent = ob_get_contents();
			ob_end_flush();

			foreach(array_pop(self::$effectList) as $tEffect) {
				$tContent = call_user_func($tEffect, $tContent);
			}

			if($uFlush) {
				echo $tContent;
			}
			
			return $tContent;
		}

		public static function flushOutput($uContent) {
			return '';
		}
	}
}

?>
