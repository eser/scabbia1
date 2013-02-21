<?php

	namespace Scabbia;

	/**
	 * Event manager which handles communication between framework parts and extensions
	 *
	 * @package Scabbia
	 */
	class events {
		/**
		 * @ignore
		 */
		public static $callbacks = array();
		/**
		 * @ignore
		 */
		public static $eventDepth = array();
		/**
		 * Indicates the event manager is currently disabled or not.
		 */
		public static $disabled = false;


		/**
		 * Makes a callback method subscribed to specified event.
		 *
		 * @param string $uEventName the event
		 * @param mixed $uCallback callback method
		 */
		public static function register($uEventName, $uCallback, $uType = 'method') {
			if(!array_key_exists($uEventName, self::$callbacks)) {
				self::$callbacks[$uEventName] = array();
			}

			self::$callbacks[$uEventName][] = array($uCallback, $uType);
		}

		/**
		 * Invokes an event.
		 *
		 * @param string $uEventName the event
		 * @param array $uEventArgs arguments for the event
		 */
		public static function invoke($uEventName, &$uEventArgs = array()) {
			if(self::$disabled) {
				return null;
			}

			if(!array_key_exists($uEventName, self::$callbacks)) {
				return null;
			}

			foreach(self::$callbacks[$uEventName] as $tCallback) {
				switch($tCallback[1]) {
				case 'loadClass':
					class_exists($tCallback[0], true);
					break;

				case 'method':
					if(is_array($tCallback[0])) {
						array_push(self::$eventDepth, get_class($tCallback[0][0]) . '::' . $tCallback[0][1] . '()');
					}
					else {
						array_push(self::$eventDepth, '\\' . $tCallback[0] . '()');
					}

					$tReturn = call_user_func_array($tCallback[0], array(&$uEventArgs));
					array_pop(self::$eventDepth);

					if($tReturn === false) {
						return false;
					}
					break;
				}
			}

			return true;
		}
	}

	?>