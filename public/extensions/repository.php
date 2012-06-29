<?php

if(extensions::isSelected('repository')) {
	class repository {
		public static $packageKey = null;
		public static $packages = array();

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
			events::register('run', events::Callback('repository::run'));

			foreach(config::get('/repository/packageList', array()) as $tPackage) {
				self::$packages[$tPackage['@name']] = array();

				foreach($tPackage['fileList'] as &$tFile) {
					self::$packages[$tPackage['@name']][] = framework::translatePath($tFile['@path']);
				}
			}
		}

		public static function run() {
			$tCheckUrlKey = config::get('/repository/routing/@repositoryCheckUrlKey', 'rep');
			$tCheckUrlValue = config::get('/repository/routing/@repositoryCheckUrlValue', '');
			$tPackageUrlKey = config::get('/repository/routing/@repositoryPackageUrlKey', $tCheckUrlKey);

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
					framework::printFiles(self::$packages[self::$packageKey]);

					// to interrupt event-chain execution
					return false;
				}
				else
				{
					throw new Exception('package not found.');
				}
			}
		}
	}
}

?>
