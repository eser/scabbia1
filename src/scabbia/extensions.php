<?php

	namespace Scabbia;

	use Scabbia\config;
	use Scabbia\events;
	use Scabbia\framework;
	
	/**
	 * Extensions manager which extends the framework capabilities with extra routines
	 *
	 * @package Scabbia
	 *
	 * @todo cache the extensions.xml.php array
	 */
	class extensions {
		/**
		 * The array of extensions' config files.
		 */
		public static $configFiles = null;


		/**
		 * Loads the extensions module.
		 */
		public static function load() {
			self::$configFiles = array();

			$tFiles = array();
			framework::glob(framework::$vendorpath . 'src/scabbia/extensions/', null, framework::GLOB_DIRECTORIES | framework::GLOB_RECURSIVE, '', $tFiles);
			if(!is_null(framework::$applicationPath)) {
				framework::glob(framework::$applicationPath . 'extensions/', null, framework::GLOB_DIRECTORIES | framework::GLOB_RECURSIVE, '', $tFiles);
			}

			foreach($tFiles as $tFile) {
				if(!is_file($tFile . 'extension.xml.php')) {
					continue;
				}

				$tSubconfig = array();
				config::loadFile($tSubconfig, $tFile . 'extension.xml.php');
				self::$configFiles[$tSubconfig['/info/name']] = array('path' => $tFile, 'config' => $tSubconfig);

				if(isset($tSubconfig['/eventList'])) {
					foreach($tSubconfig['/eventList'] as $tLoad) {
						if($tLoad['name'] == 'load') {
							call_user_func($tLoad['callback']);
							continue;
						}

						events::register($tLoad['name'], $tLoad['callback']);
					}
				}
			}
		}
	}

	?>