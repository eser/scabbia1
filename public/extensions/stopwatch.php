<?php

	class stopwatch {
		public static $markers = array();

		public static function extension_info() {
			return array(
				'name' => 'stopwatch',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'fwversion' => '1.0',
				'enabled' => true,
				'autoevents' => false,
				'depends' => array()
			);
		}

		public static function start($uName) {
			self::$markers[$uName] = microtime(true);
		}

		public static function stop($uName) {
			return microtime(true) - self::$markers[$uName];
		}
	}

?>
