<?php

	class scabbia extends controller {
		public function __construct() {
			if(framework::$development <= 0) {
				exit('why?');
			}
		}

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
			$tFilename = QPATH_BASE . 'compiled.php';
		
			ob_start();
			ob_implicit_flush(false);

			$this->build_export(($uParams == 'pseudo'));

			$tContents = ob_get_contents();
			ob_end_clean();

			$tOutput = fopen($tFilename, 'w') or exit('Unable to write to ' . $tFilename);
			fwrite($tOutput, $tContents);
			fclose($tOutput);
			
			exit('done.');
		}

		/**
		* @ignore
		*/
		private function build_export($uPseudo) {
			if($uPseudo) { // framework::$development >= 1 || 
				echo '<', '?php
	require(', var_export('framework' . QEXT_PHP), ');
?', '>';
				return;
			}

			/* BEGIN */
			echo '<', '?php
				
	$applicationDir = ', var_export(framework::$applicationPath), ';
	$development = 0;
	$runExtensions = ', var_export(framework::$runExtensions), ';

	ignore_user_abort();
	date_default_timezone_set(\'UTC\');
	setlocale(LC_ALL, \'en_US.UTF-8\');
	mb_internal_encoding(\'UTF-8\');
	mb_http_output(\'UTF-8\');

	define(\'PHP_OS_WINDOWS\', ', var_export(PHP_OS_WINDOWS), ');
	define(\'PHP_SAPI_CLI\', (PHP_SAPI == \'cli\'));
	define(\'QPATH_BASE\', ', var_export(QPATH_BASE), ');
	define(\'QPATH_CORE\', ', var_export(QPATH_CORE), ');
	define(\'QTIME_INIT\', microtime(true));
	define(\'QEXT_PHP\', ', var_export(QEXT_PHP), ');

	define(\'SCABBIA_VERSION\', ', var_export(SCABBIA_VERSION), ');
	define(\'INCLUDED\', ', var_export(INCLUDED), ');
	define(\'COMPILED\', true);

	define(\'OUTPUT_NOHANDLER\', ', var_export(OUTPUT_NOHANDLER), ');
	define(\'OUTPUT_GZIP\', ', var_export(OUTPUT_GZIP), ');
	define(\'OUTPUT_MULTIBYTE\', ', var_export(OUTPUT_MULTIBYTE), ');
?', '>';

			echo php_strip_whitespace(QPATH_CORE . 'includes/patches.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'includes/config.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'includes/events.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'includes/framework.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'includes/extensions.main' . QEXT_PHP);

			echo '<', '?php config::set(', config::export(), '); framework::init(); framework::load(); ?', '>';

			framework::printIncludeFilesFromConfig();

			echo '<', '?php extensions::load(); framework::run(); extensions::run(); ?', '>';
			/* END   */
		}
		
		/**
		* Purges the files in given directory.
		*
		* @param $uFolder string destination directory
		*/
		public function purge() {
			$this->purgeFolder(framework::$applicationPath . 'writable/cache');
			$this->purgeFolder(framework::$applicationPath . 'writable/logs');
			$this->purgeFolder(framework::$applicationPath . 'writable/mediaCache');

			exit('done.');
		}

		/**
		* @ignore
		*/
		private function purgeFolder($uFolder) {
			foreach(glob3($uFolder . '/*', false, true) as $tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				unlink($tFilename);
			}
		}
	}
	
?>