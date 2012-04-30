<?php

	class Events {
		public static $callbacks = array();
		public static $eventDepth = array();
		public static $disabled = false;
	
		public static function register($uEventName, $uCallback) {
			if(!array_key_exists($uEventName, self::$callbacks)) {
				self::$callbacks[$uEventName] = array();
			}
			
			self::$callbacks[$uEventName][] = $uCallback;
		}

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
		
		public static function setDisabled($uDisabled) {
			self::$disabled = $uDisabled;
		}
		
		public static function getEventDepth() {
			return self::$eventDepth;
		}
		
		// usage1: Events::Callback('method', $this));
		// usage2: Events::Callback('static::method'));
		public static function Callback($uCallbackMethod, &$uCallbackObject = null) {
			if(func_num_args() >= 2) {
				return array(&$uCallbackObject, $uCallbackMethod);
			}
			
			return $uCallbackMethod;
		}
	}

?>
