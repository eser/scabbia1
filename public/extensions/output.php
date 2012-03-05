<?php

	class output {
		private static $effectList = array();

		public static function extension_info() {
			return array(
				'name' => 'output',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'fwversion' => '1.0',
				'enabled' => true,
				'autoevents' => false,
				'depends' => array()
			);
		}

		public static function extension_load() {
			// for(;ob_get_level() > 0;ob_end_clean());
		}

		public static function begin() {
			ob_start(array('output', 'flushOutput'));
			ob_implicit_flush(0);

			$tArgs = func_get_args();
			array_push(self::$effectList, $tArgs);
		}

		public static function &end($uFlush = true) {
			$tContent = ob_get_contents();
			ob_end_flush();

			Events::invoke('output', array(
				'content' => &$tContent,
				'effects' => array_pop(self::$effectList)
			));

			if($uFlush) {
				echo $tContent;
			}
			
			return $tContent;
		}

		public static function flushOutput($uContent) {
			return '';
		}
	}

?>
