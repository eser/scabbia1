<?php

	/**
	 * Media Extension
	 *
	 * @package Scabbia
	 * @subpackage media
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 *
	 * @todo add watermark
	 * @todo write text w/ truetype fonts
	 * @todo integrate with cache extension
	 */
	class media {
		/**
		 * @ignore
		 */
		public static $cachePath;
		/**
		 * @ignore
		 */
		public static $cacheAge;

		/**
		 * @ignore
		 */
		public static function extensionLoad() {
			self::$cachePath = framework::writablePath('cache/media/');
			self::$cacheAge = intval(config::get('/media/cacheAge', '120'));
		}

		/**
		 * @ignore
		 */
		public static function open($uSource, $uOriginalFilename = null) {
			return new mediaFile($uSource, $uOriginalFilename);
		}

		/**
		 * @ignore
		 */
		public static function calculateHash() {
			$uArgs = func_get_args();

			return implode('_', $uArgs);
		}

		/**
		 * @ignore
		 */
		public static function garbageCollect() {
			$tDirectory = new DirectoryIterator(self::$cachePath);

			clearstatcache();
			foreach($tDirectory as $tFile) {
				if(!$tFile->isFile()) {
					continue;
				}

				if(time() - $tFile->getMTime() < self::$cacheAge) {
					continue;
				}

				unlink($tFile->getPathname());
			}
		}
	}

?>