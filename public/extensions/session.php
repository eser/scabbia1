<?php

if(Extensions::isSelected('session')) {
	class session {
		public static $id = null;
		public static $data = null;
		public static $flashdata_loaded = null;
		public static $flashdata_next = array();
		public static $sessionName;
		public static $sessionLife;
		public static $isModified = false;
		public static $keyphase = null;
		public static $directory;

		public static function extension_info() {
			return array(
				'name' => 'session',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('io', 'http')
			);
		}
		
		public static function extension_load() {
			self::$sessionName = Config::get('/session/cookie/@name', 'sessid');
			self::$sessionLife = intval(Config::get('/session/cookie/@life', '0'));
			self::$keyphase = Config::get('/session/cookie/@keyphase', null);

			if(array_key_exists(self::$sessionName, $_COOKIE)) {
				self::$id = $_COOKIE[self::$sessionName];
			}

			Events::register('output', Events::Callback('session::output'));
			
			self::$directory = QPATH_APP . 'writable/sessions/';
		}

		public static function output() {
			if(self::$isModified) {
				self::save();
			}
		}

		private static function open() {
			if(!is_null(self::$id)) {
				$tIpCheck = (bool)Config::get('/session/cookie/@ipCheck', '0');
				$tUACheck = (bool)Config::get('/session/cookie/@uaCheck', '1');

				$tFilename = self::$directory . self::$id;

				if(file_exists($tFilename)) {
					$tData = io::readSerialize($tFilename, self::$keyphase);

					if(
						(self::$sessionLife <= 0 || $tData['lastmod'] + self::$sessionLife >= time()) &&
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

		public static function save() {
			$tKeyphase = Config::get('/session/cookie/@keyphase', null);

			if(is_null(self::$id)) {
				self::$id = io::sanitize(string::generateUuid());
			}

			$tFilename = self::$directory . self::$id;

			if(self::$sessionLife > 0) {
				$tCookieLife = time() + self::$sessionLife;
			}
			else {
				$tCookieLife = 0;
			}

			setcookie(self::$sessionName, self::$id, $tCookieLife, '/');

			io::writeSerialize($tFilename, array(
					'data' => self::$data,
					'flashdata' => self::$flashdata_next,
					'lastmod' => time(),
					'ip' => $_SERVER['REMOTE_ADDR'],
					'ua' => $_SERVER['HTTP_USER_AGENT']
				),
				self::$keyphase
			);

			self::$isModified = false;
		}

		public static function destroy() {
			if(!is_null(self::$data)) {
				self::open();
			}

			if(is_null(self::$id)) {
				return;
			}

			$tFilename = self::$directory . self::$id;

			setcookie(self::$sessionName, '', time() - 3600, '/');

			if(file_exists($tFilename)) {
				unlink($tFilename);
			}

			self::$id = null;
			self::$data = null;
			self::$flashdata_loaded = null;

			self::$isModified = false;
		}

		public static function get($uKey, $uDefault = null) {
			if(is_null(self::$data)) {
				self::open();
			}

			if(!array_key_exists($uKey, self::$data)) {
				return $uDefault;
			}

			return self::$data[$uKey];
		}

		public static function set($uKey, $uValue) {
			if(is_null(self::$data)) {
				self::open();
			}

			self::$data[$uKey] = $uValue;
			self::$isModified = true;
		}

		public static function remove($uKey) {
			if(is_null(self::$data)) {
				self::open();
			}

			unset(self::$data[$uKey]);
			self::$isModified = true;
		}

		public static function exists($uKey) {
			if(is_null(self::$data)) {
				self::open();
			}

			return array_key_exists($uKey, self::$data);
		}


		public static function getFlash($uKey, $uDefault = null) {
			if(is_null(self::$data)) {
				self::open();
			}

			if(!array_key_exists($uKey, self::$flashdata_loaded)) {
				return $uDefault;
			}

			return self::$flashdata_loaded[$uKey];
		}

		public static function setFlash($uKey, $uValue) {
			self::$flashdata_next[$uKey] = $uValue;
			self::$isModified = true;
		}

		public static function removeFlash($uKey, $uValue) {
			unset(self::$flashdata_next[$uKey]);
			self::$isModified = true;
		}

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

		public static function existsFlash($uKey) {
			if(is_null(self::$data)) {
				self::open();
			}

			return array_key_exists($uKey, self::$flashdata_loaded);
		}
	}
}

?>
