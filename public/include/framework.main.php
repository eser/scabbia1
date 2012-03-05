<?php

	class Framework {
		private static $includePaths = array();
	
		public static function load() {
			$tIncludes = Config::get('/includeList', array());

			foreach($tIncludes as &$tInclude) {
				if(substr($tInclude['@path'], 0, 6) == '{core}') {
					self::$includePaths[] = QPATH_CORE . substr($tInclude['@path'], 6);
					continue;
				}

				if(substr($tInclude['@path'], 0, 5) == '{app}') {
					self::$includePaths[] = QPATH_APP . substr($tInclude['@path'], 5);
					continue;
				}

				self::$includePaths[] = $tInclude['@path'];
			}

			if(!COMPILED) {
				self::includeFiles();
			}
		}

		public static function run() {
			if(OUTPUT_GZIP) {
				ob_start('ob_gzhandler');
			}

			if(OUTPUT_MULTIBYTE) {
				ob_start('mb_output_handler');
			}

			Events::invoke('run', array());
		}

		public static function includeFiles() {
			foreach(self::$includePaths as &$tPath) {
				foreach(glob($tPath, GLOB_MARK|GLOB_NOSORT) as $tFilename) {
					if(substr($tFilename, -1) == '/') {
						continue;
					}

					require($tFilename);
				}
			}
		}
		
		private static function printIncludeFiles() {
			foreach(self::$includePaths as &$tPath) {
				foreach(glob($tPath, GLOB_MARK|GLOB_NOSORT) as $tFilename) {
					if(substr($tFilename, -1) == '/') {
						continue;
					}

					echo php_strip_whitespace($tFilename);
				}
			}
		}
		
		public static function build($uFilename) {
			ob_start();

			/* BEGIN */
			echo '<', '?php
				ignore_user_abort();
				date_default_timezone_set(\'UTC\');
				setlocale(LC_ALL, \'en_US.UTF-8\');
				mb_internal_encoding(\'UTF-8\');
				mb_http_output(\'UTF-8\');

				define(\'PHP_OS_WINDOWS\', ', var_export(PHP_OS_WINDOWS), ');
				define(\'QPATH_CORE\', ', var_export(QPATH_CORE), ');
				define(\'QPATH_APP\', ', var_export(QPATH_APP), ');
				define(\'QTIME_INIT\', microtime(true));
				define(\'QEXT_PHP\', ', var_export(QEXT_PHP), ');

				define(\'SCABBIA_VERSION\', ', var_export(SCABBIA_VERSION), ');
				define(\'INCLUDED\', ', var_export(INCLUDED), ');
				define(\'COMPILED\', true);
				define(\'DEBUG\', false);

				define(\'OUTPUT_NOHANDLER\', ', var_export(OUTPUT_NOHANDLER), ');
				define(\'OUTPUT_GZIP\', ', var_export(OUTPUT_GZIP), ');
				define(\'OUTPUT_MULTIBYTE\', ', var_export(OUTPUT_MULTIBYTE), ');
			?', '>';

			echo php_strip_whitespace(QPATH_CORE . 'include/patches.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'include/config.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'include/events.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'include/framework.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'include/extensions.main' . QEXT_PHP);

			self::printIncludeFiles();

			echo '<', '?php Config::set(', Config::export(), '); Framework::load(); Extensions::load(); Framework::run(); ?', '>';
			/* END   */

			$tContents = ob_get_contents();
			ob_end_clean();

			$tOutput = fopen($uFilename, 'w') or exit('Unable to write to ' . $uFilename);
			fwrite($tOutput, $tContents);
			fclose($tOutput);
		}

		public static function purgeCompiledTemplates() {
			$tViewCompiledPath = QPATH_APP . Config::get('/mvc/views/@compiledPath', 'views/compiled');

			foreach(glob($tViewCompiledPath . '/*', GLOB_MARK|GLOB_NOSORT) as $tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				unlink($tFilename);
			}
		}
	}

?>