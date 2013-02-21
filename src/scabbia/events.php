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
		public static function register($uEventName, $uCallback) {
			if(!array_key_exists($uEventName, self::$callbacks)) {
				self::$callbacks[$uEventName] = array();
			}

			self::$callbacks[$uEventName][] = $uCallback;
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
				if(is_array($tCallback)) {
					$tCallname = array(get_class($tCallback[0]), $tCallback[1]);
				}
				else {
					$tCallname = array('\\', $tCallback);
				}

				$tKey = $tCallname[0] . '::' . $tCallname[1];
				array_push(self::$eventDepth, $tKey . '()');
				$tReturn = call_user_func_array($tCallback, array(&$uEventArgs));
				array_pop(self::$eventDepth);

				if($tReturn === false) {
					return false;
				}
			}

			return true;
		}
	}

	?>