<?php

	namespace Scabbia;

	/**
	 * @ignore
	 */
	class blackmoreScabbia {
		/**
		 * @ignore
		 */
		public static function blackmoreRegisterModules($uParms) {
			$uParms['modules']['index']['submenus'] = true;

			$uParms['modules']['index']['actions'][] = array(
				'action' => 'debug',
				'callback' => 'blackmoreScabbia::debug',
				'menutitle' => 'Debug Info'
			);

			$uParms['modules']['index']['actions'][] = array(
				'action' => 'build',
				'callback' => 'blackmoreScabbia::build',
				'menutitle' => 'Build'
			);

			$uParms['modules']['index']['actions'][] = array(
				'action' => 'purge',
				'callback' => 'blackmoreScabbia::purge',
				'menutitle' => 'Purge'
			);
		}

		/**
		 * @ignore
		 */
		public static function index() {
			auth::checkRedirect('user');

			views::viewFile('{core}views/blackmore/scabbia/index.php');
		}

		/**
		 * @ignore
		 */
		public static function debug() {
			auth::checkRedirect('admin');

			views::viewFile('{core}views/blackmore/scabbia/debug.php');
		}

		/**
		 * Builds a framework compilation.
		 *
		 * @param $uAction
		 * @param string $uModule
		 *
		 * @internal param string $uFilename output file
		 * @internal param bool $uPseudo wheater file is an pseudo compilation or not
		 */
		public static function build($uAction, $uModule = '') {
			auth::checkRedirect('admin');

			// $tStart = microtime(true);

			if(strlen($uModule) > 0) {
				$tFilename = 'compiled.' . $uModule . '.php';
			}
			else {
				$tFilename = 'compiled.php';
			}

			$tContents = self::buildExport($uModule, false);

			header('Expires: Thu, 01 Jan 1970 00:00:00 GMT', true);
			header('Pragma: public', true);
			header('Cache-Control: no-store, no-cache, must-revalidate', true);
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			header('Content-Type: application/octet-stream', true);
			header('Content-Disposition: attachment;filename=' . $tFilename, true);

			echo $tContents;

			// exit('done in ' . number_format(microtime(true) - $tStart, 4) . ' msec.');
		}

		/**
		 * @ignore
		 */
		private static function buildExport($uModule, $uPseudo) {
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
	define(\'QPATH_VENDOR\', ' . var_export(framework::$applicationPath, true) . ');
	define(\'QTIME_INIT\', microtime(true));

	define(\'SCABBIA_VERSION\', ' . var_export(SCABBIA_VERSION, true) . ');
	define(\'COMPILED\', true);

	define(\'OUTPUT_NOHANDLER\', ' . var_export(OUTPUT_NOHANDLER, true) . ');
	define(\'OUTPUT_GZIP\', ' . var_export(OUTPUT_GZIP, true) . ');

	error_reporting(' . var_export(error_reporting(), true) . ');
	ini_set(\'display_errors\', ' . var_export(ini_get('display_errors'), true) . ');
	ini_set(\'log_errors\', ' . var_export(ini_get('log_errors'), true) . ');

?' . '>');

			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'patches.php'));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_VENDOR . 'scabbia/framework.php'));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_VENDOR . 'scabbia/config.php'));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_VENDOR . 'scabbia/events.php'));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_VENDOR . 'scabbia/extensions.php'));

			$tDevelopment = framework::$development;
			framework::$development = 0;

			$tModule = framework::$module;
			framework::$module = (strlen($uModule) > 0) ? $uModule : null;

			$tConfig = config::load();
			$tExtensions = extensions::load();
			$tCompiled .= framework::printFile('<' . '?php config::$default = ' . var_export($tConfig, true) . '; extensions::$list = ' . var_export($tExtensions, true) . '; ?' . '>');

			// download files
			if(isset($tConfig['/downloadList'])) {
				foreach($tConfig['/downloadList'] as $tUrl) {
					framework::downloadFile($tUrl['filename'], $tUrl['url']);
				}
			}

			// include extensions
			$tIncludedFiles = array();

			//! autoloaded extensions?
			foreach($tConfig['/extensionList'] as $tExtensionName) {
				$tExtension = $tExtensions[$tExtensionName];

				if(isset($tExtension['config']['/includeList'])) {
					foreach($tExtension['config']['/includeList'] as $tFile) {
						$tFilename = $tExtension['path'] . $tFile;

						if(!in_array($tFilename, $tIncludedFiles, true)) {
							$tCompiled .= framework::printFile(file_get_contents($tFilename));
							$tIncludedFiles[] = $tFilename;
						}
					}
				}
			}

			// include files
			if(isset($tConfig['/includeList'])) {
				foreach($tConfig['/includeList'] as $tInclude) {
					$tIncludePath = pathinfo(framework::translatePath($tInclude));

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
		 * @internal param string $uFolder destination directory
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
			$tDirectory = framework::glob($uFolder, null, GLOB_RECURSIVE | GLOB_FILES);

			if($tDirectory === false) {
				return;
			}

			foreach($tDirectory as $tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				unlink($tFilename);
			}
		}
	}

?>