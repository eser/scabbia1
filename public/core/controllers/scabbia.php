<?php

	class scabbia extends controller {
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
			$this->view('{core}views/scabbia/index.php');
		}

		/**
		* Builds a framework compilation.
		*
		* @param $uFilename string output file
		* @param $uPseudo bool wheater file is an pseudo compilation or not
		*/
		public function build($uParams = '') {
			$tStart = microtime(true);

			$tFilename = QPATH_BASE . 'compiled.php';

			$tContents = $this->build_export(($uParams == 'pseudo'));

			$tOutput = fopen($tFilename, 'w') or exit('Unable to write to ' . $tFilename);
			fwrite($tOutput, $tContents);
			fclose($tOutput);

			exit('done in ' . number_format(microtime(true) - $tStart, 4) . ' msec.');
		}

		/**
		* @ignore
		*/
		private function &build_export($uPseudo) {
			if($uPseudo) { // framework::$development >= 1 ||
				$tPseudoCompile = '<' . '?php require(' . var_export('framework' . QEXT_PHP, true) . '); ?' . '>';
				return $tPseudoCompile;
			}

			/* BEGIN */
			$tCompiled = framework::printFile('<' . '?php

	$applicationDir = ' . var_export(framework::$applicationPath, true) . ';
	$development = 0;
	$runExtensions = ' . var_export(framework::$runExtensions, true) . ';

	ignore_user_abort();

	define(\'PHP_OS_WINDOWS\', ' . var_export(PHP_OS_WINDOWS, true) . ');
	define(\'PHP_SAPI_CLI\', (PHP_SAPI == \'cli\'));
	define(\'QPATH_BASE\', ' . var_export(QPATH_BASE, true) . ');
	define(\'QPATH_CORE\', ' . var_export(QPATH_CORE, true) . ');
	define(\'QTIME_INIT\', microtime(true));
	define(\'QEXT_PHP\', ' . var_export(QEXT_PHP, true) . ');

	define(\'SCABBIA_VERSION\', ' . var_export(SCABBIA_VERSION, true) . ');
	define(\'INCLUDED\', ' . var_export(INCLUDED, true) . ');
	define(\'COMPILED\', true);

	define(\'OUTPUT_NOHANDLER\', ' . var_export(OUTPUT_NOHANDLER, true) . ');
	define(\'OUTPUT_GZIP\', ' . var_export(OUTPUT_GZIP, true) . ');
	define(\'OUTPUT_MULTIBYTE\', ' . var_export(OUTPUT_MULTIBYTE, true) . ');
?' . '>');

			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/patches.main' . QEXT_PHP));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/config.main' . QEXT_PHP));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/events.main' . QEXT_PHP));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/framework.main' . QEXT_PHP));
			$tCompiled .= framework::printFile(file_get_contents(QPATH_CORE . 'includes/extensions.main' . QEXT_PHP));

			$tCompiled .= '<' . '?php config::set(' . config::export() . '); framework::init(); framework::load(); ?' . '>';

			foreach(framework::$includePaths as &$tPath) {
				$tFiles = glob3($tPath, false, true);

				if($tFiles == false) {
					continue;
				}

				foreach($tFiles as &$tFilename) {
					if(substr($tFilename, -1) == '/') {
						continue;
					}

					$tCompiled .= framework::printFile(file_get_contents($tFilename));
				}
			}

			$tCompiled .= '<' . '?php extensions::load(); framework::run(); extensions::run(); ?' . '>';
			/* END   */

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
			$this->purgeFolder(framework::$applicationPath . 'writable/mediaCache');

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

?>