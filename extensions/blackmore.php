<?php

if(extensions::isSelected('blackmore')) {
	/**
	* Blackmore Extension
	*
	* @package Scabbia
	* @subpackage blackmore
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class blackmore extends controller {
		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'blackmore',
				'version' => '1.0.2',
				'phpversion' => '5.2.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string', 'resources')
			);
		}

		/**
		* @ignore
		*/
		public function render(&$uAction, &$uArgs) {
			if(framework::$development <= 0) {
				return false;
			}

			return parent::render($uAction, $uArgs);
		}

		/**
		* @ignore
		*/
		public function index() {
			$this->view('{core}views/blackmore/index.php');
		}

		/**
		* Builds a framework compilation.
		*
		* @param $uFilename string output file
		* @param $uPseudo bool wheater file is an pseudo compilation or not
		*/
		public function build($uModule = '') {
			$tStart = microtime(true);

			if(strlen($uModule) > 0) {
				$tFilename = framework::$applicationPath . 'compiled.' . $uModule . '.php';
			}
			else {
				$tFilename = framework::$applicationPath . 'compiled.php';
			}

			$tContents = $this->build_export($uModule, false);

			$tOutput = fopen($tFilename, 'w') or exit('Unable to write to ' . $tFilename);
			fwrite($tOutput, $tContents);
			fclose($tOutput);

			exit('done in ' . number_format(microtime(true) - $tStart, 4) . ' msec.');
		}

		/**
		* @ignore
		*/
		private function &build_export($uModule, $uPseudo) {
			if($uPseudo) { // framework::$development >= 1 ||
				$tPseudoCompile = '<' . '?php require(' . var_export('framework' . QEXT_PHP, true) . '); ?' . '>';
				return $tPseudoCompile;
			}

			/* BEGIN */
			$tCompiled = framework::printFile('<' . '?php

	ignore_user_abort();

	date_default_timezone_set(\'UTC\');

	$tBacktrace = debug_backtrace();
	if(count($tBacktrace) <= 0) {
		trigger_error(\'Scabbia framework cannot be called directly\', E_USER_ERROR);
		exit;
	}

	define(\'PHP_OS_WINDOWS\', ' . var_export(PHP_OS_WINDOWS, true) . ');
	define(\'PHP_SAPI_CLI\', (PHP_SAPI == \'cli\'));
	define(\'PHP_SAFEMODE\', ' . var_export(PHP_SAFEMODE, true) . ');
	if(!defined(\'QPATH_BASE\')) {
		define(\'QPATH_BASE\', ' . var_export(QPATH_BASE, true) . ');
	}
	define(\'QPATH_CORE\', ' . var_export(framework::$applicationPath, true) . ');
	define(\'QTIME_INIT\', microtime(true));
	define(\'QEXT_PHP\', ' . var_export(QEXT_PHP, true) . ');

	define(\'SCABBIA_VERSION\', ' . var_export(SCABBIA_VERSION, true) . ');
	define(\'COMPILED\', true);

	define(\'OUTPUT_NOHANDLER\', ' . var_export(OUTPUT_NOHANDLER, true) . ');
	define(\'OUTPUT_GZIP\', ' . var_export(OUTPUT_GZIP, true) . ');
	define(\'OUTPUT_MULTIBYTE\', ' . var_export(OUTPUT_MULTIBYTE, true) . ');

	error_reporting(' . var_export(error_reporting(), true) . ');
	ini_set(\'display_errors\', ' . var_export(ini_get('display_errors'), true) . ');
	ini_set(\'log_errors\', ' . var_export(ini_get('log_errors'), true) . ');

?' . '>');

			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/patches.main' . QEXT_PHP));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/config.main' . QEXT_PHP));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/events.main' . QEXT_PHP));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/framework.main' . QEXT_PHP));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/extensions.main' . QEXT_PHP));

			$tDevelopment = framework::$development;
			framework::$development = 0;

			$tModule = framework::$module;
			framework::$module = (strlen($uModule) > 0) ? $uModule : null;

			$tConfig = config::load();
			$tCompiled .= framework::printFile('<' . '?php config::set(' . var_export($tConfig, true) . '); extensions::load(); ?' . '>');

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
					$tIncludePath = framework::translatePath($tInclude['@path']);

					$tFiles = glob3($tIncludePath, false);
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
		public function purge() {
			$tStart = microtime(true);

			$this->purgeFolder(framework::$applicationPath . 'writable/cache');
			$this->purgeFolder(framework::$applicationPath . 'writable/logs');

			exit('done in ' . number_format(microtime(true) - $tStart, 4) . ' msec.');
		}

		/**
		* @ignore
		*/
		private function purgeFolder($uFolder) {
			$tDirectory = glob3($uFolder . '/*', false, true);
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
}

?>