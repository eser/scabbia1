<?php

	/**
	 * Session Extension
	 *
	 * @package Scabbia
	 * @subpackage session
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends cache
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 *
	 * @todo integrate with cache extension
	 */
	class session {
		/**
		 * @ignore
		 */
		public static $id = null;
		/**
		 * @ignore
		 */
		public static $data = null;
		/**
		 * @ignore
		 */
		public static $flashdata_loaded = null;
		/**
		 * @ignore
		 */
		public static $flashdata_next = array();
		/**
		 * @ignore
		 */
		public static $sessionName;
		/**
		 * @ignore
		 */
		public static $sessionLife;
		/**
		 * @ignore
		 */
		public static $isModified = false;

		/**
		 * @ignore
		 */
		private static function open() {
			self::$sessionName = config::get('/session/cookie/name', 'sessid');

			if(config::get('/session/cookie/nameIp', true)) {
				self::$sessionName .= hash('adler32', $_SERVER['REMOTE_ADDR']);
			}

			self::$sessionLife = intval(config::get('/session/cookie/life', '0'));

			if(array_key_exists(self::$sessionName, $_COOKIE)) {
				self::$id = $_COOKIE[self::$sessionName];
			}

			if(!is_null(self::$id)) {
				$tIpCheck = (bool)config::get('/session/cookie/ipCheck', '0');
				$tUACheck = (bool)config::get('/session/cookie/uaCheck', '1');

				$tData = cache::fileGet('sessions/', self::$id, self::$sessionLife, true);
				if($tData !== false) {
					if(
						(!$tIpCheck || $tData['ip'] == $_SERVER['REMOTE_ADDR']) &&
						(!$tUACheck || $tData['ua'] == $_SERVER['HTTP_USER_AGENT'])
					) {
						self::$data = $tData['data'];
						self::$flashdata_loaded = $tData['flashdata'];

						return;
					}
				}
			}

			self::$data = array();
			self::$flashdata_loaded = array();
			self::$isModified = false;
		}

		/**
		 * @ignore
		 */
		public static function save() {
			if(!self::$isModified) {
				return;
			}

			if(is_null(self::$id)) {
				if(extensions::isLoaded('string')) {
					self::$id = string::generateUuid();
				}
				else {
					self::$id = uniqid('', true);
				}
			}

			if(self::$sessionLife > 0) {
				$tCookieLife = time() + self::$sessionLife;
			}
			else {
				$tCookieLife = 0;
			}

			setcookie(self::$sessionName, self::$id, $tCookieLife, '/');

			$tData = array(
				'data' => self::$data,
				'flashdata' => self::$flashdata_next,
				'ip' => $_SERVER['REMOTE_ADDR'],
				'ua' => $_SERVER['HTTP_USER_AGENT']
			);

			cache::fileSet('sessions/', self::$id, $tData);

			self::$isModified = false;
		}

		/**
		 * @ignore
		 */
		public static function destroy() {
			if(is_null(self::$data)) { // !is_null
				self::open();
			}

			if(is_null(self::$id)) {
				return;
			}

			setcookie(self::$sessionName, '', time() - 3600, '/');

			cache::fileDestroy('sessions/', self::$id);

			self::$id = null;
			self::$data = null;
			self::$flashdata_loaded = null;

			self::$isModified = false;
		}

		/**
		 * @ignore
		 */
		public static function &get($uKey, $uDefault = null) {
			if(is_null(self::$data)) {
				self::open();
			}

			if(!array_key_exists($uKey, self::$data)) {
				return $uDefault;
			}

			return self::$data[$uKey];
		}

		/**
		 * @ignore
		 */
		public static function set($uKey, $uValue) {
			if(is_null(self::$data)) {
				self::open();
			}

			self::$data[$uKey] = $uValue;
			self::$isModified = true;
		}

		/**
		 * @ignore
		 */
		public static function remove($uKey) {
			if(is_null(self::$data)) {
				self::open();
			}

			unset(self::$data[$uKey]);
			self::$isModified = true;
		}

		/**
		 * @ignore
		 */
		public static function exists($uKey) {
			if(is_null(self::$data)) {
				self::open();
			}

			return array_key_exists($uKey, self::$data);
		}

		/**
		 * @ignore
		 */
		public static function getKeys() {
			if(is_null(self::$data)) {
				self::open();
			}

			return array_keys(self::$data);
		}

		/**
		 * @ignore
		 */
		public static function getFlash($uKey, $uDefault = null) {
			if(is_null(self::$data)) {
				self::open();
			}

			if(!array_key_exists($uKey, self::$flashdata_loaded)) {
				return $uDefault;
			}

			self::$isModified = true;

			return self::$flashdata_loaded[$uKey];
		}

		/**
		 * @ignore
		 */
		public static function setFlash($uKey, $uValue) {
			if(is_null(self::$data)) {
				self::open();
			}

			self::$flashdata_next[$uKey] = $uValue;
			self::$isModified = true;
		}

		/**
		 * @ignore
		 */
		public static function removeFlash($uKey, $uValue) {
			if(is_null(self::$data)) {
				self::open();
			}

			unset(self::$flashdata_next[$uKey]);
			self::$isModified = true;
		}

		/**
		 * @ignore
		 */
		public static function keepFlash($uKey, $uDefault) {
			if(is_null(self::$data)) {
				self::open();
			}

			if(!array_key_exists($uKey, self::$flashdata_loaded)) {
				self::$flashdata_next[$uKey] = $uDefault;
			}
			else {
				self::$flashdata_next[$uKey] = self::$flashdata_loaded[$uKey];
			}

			self::$isModified = true;
		}

		/**
		 * @ignore
		 */
		public static function existsFlash($uKey) {
			if(is_null(self::$data)) {
				self::open();
			}

			return array_key_exists($uKey, self::$flashdata_loaded);
		}

		/**
		 * @ignore
		 */
		public static function getKeysFlash() {
			if(is_null(self::$data)) {
				self::open();
			}

			return array_keys(self::$flashdata_loaded);
		}

		/**
		 * @ignore
		 */
		public static function export($tOutput = true) {
			if(is_null(self::$data)) {
				self::open();
			}

			if(extensions::isLoaded('string')) {
				return string::vardump(self::$data, $tOutput);
			}

			return print_r(self::$data, $tOutput);
		}
	}

?>