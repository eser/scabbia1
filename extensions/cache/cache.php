<?php

if(extensions::isSelected('cache')) {
	/**
	* Cache Extension
	*
	* @package Scabbia
	* @subpackage cache
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends io
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class cache {
		/**
		* @ignore
		*/
		public static $defaultAge;
		/**
		* @ignore
		*/
		public static $keyphase;
		/**
		* @ignore
		*/
		public static $storage = null;
		/**
		* @ignore
		*/
		public static $storageObject = null;

		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'cache',
				'version' => '1.0.2',
				'phpversion' => '5.2.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('io')
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
			self::$defaultAge = intval(config::get(config::MAIN, '/cache/defaultAge', '120'));
			self::$keyphase = config::get(config::MAIN, '/cache/keyphase', '');

			$tStorage = config::get(config::MAIN, '/cache/storage', '');
			if(strlen($tStorage) > 0) {
				self::$storage = parse_url($tStorage);
			}
		}

		/**
		* @ignore
		*/
		public static function storageOpen() {
			if(!is_null(self::$storageObject)) {
				return;
			}

			if(self::$storage['scheme'] == 'memcache' && extension_loaded('memcache')) {
				self::$storageObject = new Memcache();
				self::$storageObject->connect(self::$storage['host'], self::$storage['port']);
				return;
			}
		}

		/**
		* @ignore
		*/
		public static function storageGet($uKey) {
			self::storageOpen();

			return self::$storageObject->get($uKey);
		}

		/**
		* @ignore
		*/
		public static function storageSet($uKey, $uValue, $uAge = -1) {
			self::storageOpen();

			// age
			if($uAge == -1) {
				$uAge = self::$defaultAge;
			}

			self::$storageObject->set($uKey, $uValue, 0, $uAge);
		}

		/**
		* @ignore
		*/
		public static function storageDestroy($uKey) {
			self::storageOpen();

			self::$storageObject->delete($uKey);
		}

		/**
		* @ignore
		*/
		public static function filePath($uFolder, $uFilename, $uAge = -1, $uIncludeAll = false) {
			// path
			$tPath = framework::writablePath('cache/' . $uFolder . io::sanitize($uFilename, $uIncludeAll));

			// age
			if($uAge == -1) {
				$uAge = self::$defaultAge;
			}

			// check
			if(
				!file_exists($tPath) ||
				($uAge != 0 && time() - filemtime($tPath) >= $uAge)
			) {
				return array(false, $tPath);
			}

			return array(true, $tPath);
		}

		/**
		* @ignore
		*/
		public static function fileGet($uFolder, $uFilename, $uAge = -1, $uIncludeAll = false) {
			// path
			$tPath = self::filePath($uFolder, $uFilename, $uAge, $uIncludeAll);

			//! ambiguous return value
			if(!$tPath[0]) {
				return false;
			}

			// content
			return io::readSerialize($tPath[1], self::$keyphase);
		}

		/**
		* @ignore
		*/
		public static function fileGetUrl($uKey, $uUrl, $uAge = -1) {
			$tFile = self::filePath('url/', $uKey, $uAge, true);

			if(!$tFile[0]) {
				$tContent = file_get_contents($uUrl);
				io::write($tFile[1], $tContent);
				return $tContent;
			}

			return io::read($tFile[1]);
		}

		/**
		* @ignore
		*/
		public static function fileSet($uFolder, $uFilename, $uObject) {
			// path
			$tPath = framework::writablePath('cache/' . $uFolder . io::sanitize($uFilename));

			// content
			io::writeSerialize($tPath, $uObject, self::$keyphase);

			return $tPath;
		}

		/**
		* @ignore
		*/
		public static function fileDestroy($uFolder, $uFilename) {
			$tPath = framework::writablePath('cache/' . $uFolder);
			io::destroy($tPath . io::sanitize($uFilename));
		}

		/**
		* @ignore
		*/
		public static function fileGarbageCollect($uFolder, $uAge = -1) {
			// path
			$tPath = framework::writablePath('cache/' . $uFolder);
			$tDirectory = new DirectoryIterator($tPath);

			// age
			if($uAge == -1) {
				$uAge = self::$defaultAge;
			}

			clearstatcache();
			foreach($tDirectory as $tFile) {
				if(!$tFile->isFile()) {
					continue;
				}

				if(time() - $tFile->getMTime() < $tAge) {
					continue;
				}

				io::destroy($tFile->getPathname());
			}
		}
	}
}

?>