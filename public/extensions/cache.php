<?php

if(extensions::isSelected('cache')) {
	/**
	* Cache Extension
	*
	* @package Scabbia
	* @subpackage LayerExtensions
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
		public static function extension_info() {
			return array(
				'name' => 'cache',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('io')
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
			self::$defaultAge = intval(config::get('/cache/@defaultAge', '120'));
			self::$keyphase = config::get('/cache/@keyphase', null);
		}

		/**
		* @ignore
		*/
		public static function getPath($uFolder, $uFilename, $uAge = -1) {
			// path
			$tPath = framework::writablePath('cache/' . $uFolder . io::sanitize($uFilename));

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
		public static function get($uFolder, $uFilename, $uAge = -1) {
			// path
			$tPath = self::getPath($uFolder, $uFilename, $uAge);

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
		public static function set($uFolder, $uFilename, $uObject) {
			// path
			$tPath = framework::writablePath('cache/' . $uFolder . io::sanitize($uFilename));

			// content
			io::writeSerialize($tPath, $uObject, self::$keyphase);

			return $tPath;
		}

		/**
		* @ignore
		*/
		public static function destroy($uFolder, $uFilename) {
			$tPath = framework::writablePath('cache/' . $uFolder);
			io::destroy($tPath . io::sanitize($uFilename));
		}

		/**
		* @ignore
		*/
		public static function garbageCollect($uFolder, $uAge) {
			// path
			$tPath = framework::writablePath('cache/' . $uFolder);
			$tDirectory = new DirectoryIterator($tPath);

			// age
			if($uAge > 0) {
				$tAge = $uAge;
			}
			else {
				$tAge = self::$defaultAge;
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