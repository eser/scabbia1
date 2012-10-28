<?php

	/**
	* Blackmore Extension: Scabbia Section
	*
	* @package Scabbia
	* @subpackage blackmore_scabbia
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends string, resources, auth, validation, http
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class blackmore_scabbia {
		/**
		* @ignore
		*/
		public static function blackmore_registerModules($uParms) {
			$uParms['modules']['scabbia'] = array(
				'title' => 'Scabbia',
				'callback' => 'blackmore_scabbia::index',
				'submenus' => true,
				'actions' => array(
					array(
						'action' => 'debug',
						'callback' => 'blackmore_scabbia::debug',
						'menutitle' => 'Debug Info'
					),
					array(
						'action' => 'build',
						'callback' => 'blackmore_scabbia::build',
						'menutitle' => 'Build'
					),
					array(
						'action' => 'purge',
						'callback' => 'blackmore_scabbia::purge',
						'menutitle' => 'Purge'
					)
				)
			);
		}

		/**
		* @ignore
		*/
		public static function index() {
			auth::checkRedirect('user');

			mvc::viewFile('{core}views/blackmore/scabbia/index.php');
		}

		/**
		* @ignore
		*/
		public static function debug() {
			auth::checkRedirect('admin');

			mvc::viewFile('{core}views/blackmore/scabbia/debug.php');
		}
		
		/**
		* Builds a framework compilation.
		*
		* @param $uFilename string output file
		* @param $uPseudo bool wheater file is an pseudo compilation or not
		*/
		public static function build($uModule = '') {
			auth::checkRedirect('admin');

			$tStart = microtime(true);

			if(strlen($uModule) > 0) {
				$tFilename = framework::$applicationPath . 'compiled.' . $uModule . '.php';
			}
			else {
				$tFilename = framework::$applicationPath . 'compiled.php';
			}

			$tContents = self::build_export($uModule, false);

			$tOutput = fopen($tFilename, 'w') or exit('Unable to write to ' . $tFilename);
			fwrite($tOutput, $tContents);
			fclose($tOutput);

			exit('done in ' . number_format(microtime(true) - $tStart, 4) . ' msec.');
		}

		/**
		* @ignore
		*/
		private static function &build_export($uModule, $uPseudo) {
			if($uPseudo) { // framework::$development >= 1 ||
				$tPseudoCompile = '<' . '?php require(' . var_export('framework.php', true) . '); ?' . '>';
				return $tPseudoCompile;
			}

			/* BEGIN */
			$tCompiled = framework::printFile('<' . '?php

	ignore_user_abort();

	define(\'PHP_SAPI_CLI\', (PHP_SAPI == \'cli\'));
	define(\'PHP_SAFEMODE\', ' . var_export(PHP_SAFEMODE, true) . ');
	if(!defined(\'QPATH_BASE\')) {
		define(\'QPATH_BASE\', ' . var_export(QPATH_BASE, true) . ');
	}
	define(\'QPATH_CORE\', ' . var_export(framework::$applicationPath, true) . ');
	define(\'QTIME_INIT\', microtime(true));

	define(\'SCABBIA_VERSION\', ' . var_export(SCABBIA_VERSION, true) . ');
	define(\'COMPILED\', true);

	define(\'OUTPUT_NOHANDLER\', ' . var_export(OUTPUT_NOHANDLER, true) . ');
	define(\'OUTPUT_GZIP\', ' . var_export(OUTPUT_GZIP, true) . ');

	error_reporting(' . var_export(error_reporting(), true) . ');
	ini_set(\'display_errors\', ' . var_export(ini_get('display_errors'), true) . ');
	ini_set(\'log_errors\', ' . var_export(ini_get('log_errors'), true) . ');

?' . '>');

			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/patches.main.php'));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/framework.main.php'));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/config.main.php'));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/events.main.php'));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/extensions.main.php'));

			$tDevelopment = framework::$development;
			framework::$development = 0;

			$tModule = framework::$module;
			framework::$module = (strlen($uModule) > 0) ? $uModule : null;

			$tConfig = config::load();
			$tCompiled .= framework::printFile('<' . '?php config::set(config::MAIN, ' . var_export($tConfig, true) . '); extensions::load(); ?' . '>');

			// downloads
			if(isset($tConfig['/downloadList'])) {
				foreach($tConfig['/downloadList'] as &$tUrl) {
					framework::downloadFile($tUrl['@filename'], $tUrl['@url']);
				}
			}

			// includes
			if(isset($tConfig['/includeList'])) {
				$tIncludedFiles = array();
				foreach($tConfig['/includeList'] as &$tInclude) {
					$tIncludePath = pathinfo(framework::translatePath($tInclude['@path']));

					$tFiles = framework::glob($tIncludePath['dirname'] . '/', $tIncludePath['basename'], GLOB_FILES);
					if($tFiles !== false) {
						foreach($tFiles as $tFilename) {
							if(substr($tFilename, -1) == '/') {
								continue;
							}

							if(!in_array($tFilename, $tIncludedFiles, true)) {
								$tCompiled .= framework::printFile(file_get_contents($tFilename));
								$tIncludedFiles[] = $tFilename;
							}
						}
					}
				}
			}
			/* END   */

			framework::$development = $tDevelopment;
			framework::$module = $tModule;

			return $tCompiled;
		}

		/**
		* Purges the files in given directory.
		*
		* @param $uFolder string destination directory
		*/
		public static function purge() {
			auth::checkRedirect('admin');

			$tStart = microtime(true);

			self::purgeFolder(framework::$applicationPath . 'writable/cache/');
			self::purgeFolder(framework::$applicationPath . 'writable/logs/');

			exit('done in ' . number_format(microtime(true) - $tStart, 4) . ' msec.');
		}

		/**
		* @ignore
		*/
		private static function purgeFolder($uFolder) {
			$tDirectory = framework::glob($uFolder, null, GLOB_RECURSIVE|GLOB_FILES);

			if($tDirectory === false) {
				return;
			}

			foreach($tDirectory as &$tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				unlink($tFilename);
			}
		}
	}

?>