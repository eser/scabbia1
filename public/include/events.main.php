<?php

	/**
	* Event manager which handles communication between framework parts and extensions
	*
	* @package Scabbia
	* @subpackage Core
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
		* Shows weather event manager is currently disabled or not.
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
		public static function invoke($uEventName, $uEventArgs = array()) {
			if(self::$disabled) {
				return;
			}
			
			if(!array_key_exists($uEventName, self::$callbacks)) {
				return;
			}

			foreach(self::$callbacks[$uEventName] as &$tCallback) {
				if(is_array($tCallback)) {
					$tCallname = array(get_class($tCallback[0]), $tCallback[1]);
				} else {
					$tCallname = array('GLOBALS', $tCallback);
				}

				$tKey = $tCallname[0] . '::' . $tCallname[1];
				array_push(self::$eventDepth, $tKey . '()');
				if(call_user_func($tCallback, $uEventArgs) === false) {
					break;
				}
				array_pop(self::$eventDepth);
			}
		}

		/**
		* Disables or Enables the event manager.
		*
		* @param bool $uDisabled disabled status
		*/
		public static function setDisabled($uDisabled) {
			self::$disabled = $uDisabled;
		}

		/**
		* Gets the event depth.
		*
		* @return array event depth.
		*/
		public static function getEventDepth() {
			return self::$eventDepth;
		}
		
		// usage1: events::callback('method', $this));
		// usage2: events::callback('static::method'));
		/**
		* Returns a callback.
		*
		* @param string $uCallbackMethod method name
		* @param object $uCallbackObject container object
		*/
		public static function callback($uCallbackMethod, &$uCallbackObject = null) {
			if(func_num_args() >= 2) {
				return array(&$uCallbackObject, $uCallbackMethod);
			}
			
			return $uCallbackMethod;
		}
	}

?>
