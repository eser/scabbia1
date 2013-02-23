<?php

	namespace Scabbia;

	/**
	 * Event manager which handles communication between framework parts and extensions.
	 *
	 * @package Scabbia
	 */
	class events {
		/**
		 * The array of registered callbacks for the events
		 */
		public static $callbacks = array();
		/**
		 * Event depth
		 */
		public static $eventDepth = array();
		/**
		 * Indicates the event manager is currently disabled or not
		 */
		public static $disabled = false;


		/**
		 * Makes a callback method subscribed to specified event.
		 *
		 * @param string $uEventName the event
		 * @param mixed $uCallback callback method
		 */
		public static function register($uEventName, $uType, $uValue, $uPriority = 10) {
			if(!array_key_exists($uEventName, self::$callbacks)) {
				self::$callbacks[$uEventName] = array();
			}

			self::$callbacks[$uEventName][] = array($uType, $uValue, $uPriority);
			usort(self::$callbacks[$uEventName], function($uFirst, $uSecond) {
				return strnatcmp($uFirst[2], $uSecond[2]);
			});
		}

		/**
		 * Invokes an event.
		 *
		 * @param string $uEventName the event
		 * @param array $uEventArgs arguments for the event
		 *
		 * @uses invokeSingle()
		 */
		public static function invoke($uEventName, &$uEventArgs = array()) {
			if(self::$disabled) {
				return null;
			}

			if(!array_key_exists($uEventName, self::$callbacks)) {
				return null;
			}

			foreach(self::$callbacks[$uEventName] as $tCallback) {
				self::invokeSingle($tCallback[0], $tCallback[1]);
			}

			return true;
		}

		/**
		 * Executes a single event.
		 *
		 * @param string $uType the type of event
		 * @param mixed $uValue the value of event
		 */
		public static function invokeSingle($uType, $uValue) {
			if(self::$disabled) {
				return null;
			}

			switch($uType) {
			case 'loadClass':
				class_exists($uValue, true);
				break;

			case 'include':
				include(framework::translatePath($uValue));
				break;

			case 'callback':
				if(is_array($uValue)) {
					array_push(self::$eventDepth, get_class($uValue[0]) . '::' . $uValue[1] . '()');
				}
				else {
					array_push(self::$eventDepth, '\\' . $uValue . '()');
				}

				$tReturn = call_user_func_array($uValue, array(&$uEventArgs));
				array_pop(self::$eventDepth);

				if($tReturn === false) {
					return false;
				}
				break;
			}
		}
	}

	?>