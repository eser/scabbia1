<?php

if(Extensions::isSelected('repository')) {
	class repository {
		private static $packageKey = null;
		private static $packages = array();

		public static function extension_info() {
			return array(
				'name' => 'repository',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('io')
			);
		}
		
		public static function extension_load() {
			if(COMPILED) {
				Events::register('run', Events::Callback('repository::run'));
			}

			foreach(Config::get('/repository/packageList', array()) as $tPackage) {
				self::$packages[$tPackage['@name']] = array();

				foreach($tPackage['fileList'] as &$tFile) {
					self::$packages[$tPackage['@name']][] = Framework::translatePath($tFile['@path']);
				}
			}
		}

		public static function run() {
			$tCheckUrlKey = Config::get('/repository/routing/@repositoryCheckUrlKey', 'rep');
			$tCheckUrlValue = Config::get('/repository/routing/@repositoryCheckUrlValue', '');
			$tPackageUrlKey = Config::get('/repository/routing/@repositoryPackageUrlKey', $tCheckUrlKey);

			if(array_key_exists($tCheckUrlKey, $_GET)) {
				if(strlen($tCheckUrlValue) == 0) {
					self::$packageKey = $_GET[$tPackageUrlKey];
				}
				else if($_GET[$tCheckUrlKey] == $tCheckUrlValue) {
					self::$packageKey = $_GET[$tPackageUrlKey];
				}
			}

			if(isset(self::$packageKey)) {
				if(array_key_exists(self::$packageKey, self::$packages)) {
					$tMimetype = io::getMimeType('phps');

					header('Content-Type: ' . $tMimetype, true);
					Framework::printFiles(self::$packages[self::$packageKey]);

					// to interrupt event-chain execution
					return false;
				}
				else
				{
					throw new Exception('package not found.');
				}
			}
		}

		public static function getPackageKey() {
			return self::$packageKey;
		}
	}
}

?>
