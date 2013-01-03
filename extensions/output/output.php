<?php

	/**
	 * Output Extension
	 *
	 * @package Scabbia
	 * @subpackage output
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class output {
		/**
		 * @ignore
		 */
		public static $effectList = array();

		/**
		 * @ignore
		 */
		public static function begin() {
			ob_start('output::flushOutput');
			ob_implicit_flush(false);

			$tArgs = func_get_args();
			array_push(self::$effectList, $tArgs);
		}

		/**
		 * @ignore
		 */
		public static function end($uFlush = true) {
			$tContent = ob_get_clean();

			foreach(array_pop(self::$effectList) as $tEffect) {
				$tContent = call_user_func($tEffect, $tContent);
			}

			if($uFlush) {
				echo $tContent;
			}

			return $tContent;
		}

		/**
		 * @ignore
		 */
		public static function flushOutput($uContent) {
			return '';
		}
	}

?>