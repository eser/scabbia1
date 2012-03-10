<?php

	class Framework {
		public static $includePaths = array();
		public static $downloadUrls = array();
		public static $development;
		public static $debug;
		public static $repository;
		
		public static function translatePath($uPath) {
			if(substr($uPath, 0, 6) == '{core}') {
				return QPATH_CORE . substr($uPath, 6);
			}

			if(substr($uPath, 0, 5) == '{app}') {
				return QPATH_APP . substr($uPath, 5);
			}

			return $uPath;
		}
	
		public static function load() {
			self::$development = Config::get('/options/development/@value', '0');
			self::$debug = Config::get('/options/debug/@value', '0');
			self::$repository = Config::get('/options/repository/@value', '0');

			if(!COMPILED) {
				// downloads
				$tDownloads = Config::get('/downloadList', array());

				foreach($tDownloads as &$tDownload) {
					self::$downloadUrls[$tDownload['@filename']] = $tDownload['@url'];
				}

				self::downloadFiles();

				// includes
				$tIncludes = Config::get('/includeList', array());

				foreach($tIncludes as &$tInclude) {
					self::$includePaths[] = self::translatePath($tInclude['@path']);
				}

				self::includeFilesFromConfig();
			}
		}

		public static function run() {
			if(OUTPUT_GZIP && !PHP_SAPI_CLI && Config::get('/options/gzip/@value', '1') != '0') {
				ob_start('ob_gzhandler');
			}

			if(OUTPUT_MULTIBYTE) {
				ob_start('mb_output_handler');
			}

			if(!COMPILED) {
				foreach(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $tValue) {
					if($tValue['type'] == 'include') {
						$tIncluded = true;
					}
				}

				if(PHP_SAPI_CLI) {
					$tParameters = implode(' ', array_slice($GLOBALS['argv'], 1));
				}
				else {
					$tParameters = $_SERVER['QUERY_STRING'];
				}

				if(!isset($tIncluded)) {
					if(self::$development) {
						if($tParameters == 'build') {
							self::build('index.php');
							self::purgeTemp();
							self::purgeCompiledTemplates();

							echo 'build done.';

							return;
						}
					}

					if(self::$repository) {
						if(substr($tParameters, 0, 4) == 'rep:') {
							// $tFile = QPATH_CORE . 'extensions/' . substr($tParameters, 4);
							$tFile = QPATH_CORE . substr($tParameters, 4);
							echo php_strip_whitespace($tFile);

							return;
						}
					}
				}
			}

			Events::invoke('run', array());
		}

		public static function downloadFiles() {
			foreach(self::$downloadUrls as $tFilename => &$tUrl) {
				self::downloadFile($tFilename, $tUrl);
			}
		}

		public static function downloadFile($uFile, $uUrl) {
			$tFilePath = QPATH_APP . 'downloaded/' . $uFile;
			if(file_exists($tFilePath)) {
				return false;
			}

			$tHandle = fopen($tFilePath, 'w');
			$tContent = file_get_contents($uUrl);
			fwrite($tHandle, $tContent);
			fclose($tHandle);

			return true;
		}

		private static function includeFilesFromConfig() {
			foreach(self::$includePaths as &$tPath) {
				foreach(glob($tPath, GLOB_MARK|GLOB_NOSORT) as $tFilename) {
					if(substr($tFilename, -1) == '/') {
						continue;
					}

					require($tFilename);
				}
			}
		}
		
		private static function printIncludeFilesFromConfig() {
			foreach(self::$includePaths as &$tPath) {
				self::printFiles(glob($tPath, GLOB_MARK|GLOB_NOSORT));
			}
		}

		public static function printFiles($uArray) {
			foreach($uArray as &$tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				echo php_strip_whitespace($tFilename);
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
				define(\'PHP_SAPI_CLI\', (PHP_SAPI == \'cli\'));
				define(\'QPATH_CORE\', ', var_export(QPATH_CORE), ');
				define(\'QPATH_APP\', ', var_export(QPATH_APP), ');
				define(\'QTIME_INIT\', microtime(true));
				define(\'QEXT_PHP\', ', var_export(QEXT_PHP), ');

				define(\'SCABBIA_VERSION\', ', var_export(SCABBIA_VERSION), ');
				define(\'INCLUDED\', ', var_export(INCLUDED), ');
				define(\'COMPILED\', true);

				define(\'OUTPUT_NOHANDLER\', ', var_export(OUTPUT_NOHANDLER), ');
				define(\'OUTPUT_GZIP\', ', var_export(OUTPUT_GZIP), ');
				define(\'OUTPUT_MULTIBYTE\', ', var_export(OUTPUT_MULTIBYTE), ');
			?', '>';

			echo php_strip_whitespace(QPATH_CORE . 'include/patches.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'include/config.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'include/events.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'include/framework.main' . QEXT_PHP);
			echo php_strip_whitespace(QPATH_CORE . 'include/extensions.main' . QEXT_PHP);

			self::printIncludeFilesFromConfig();

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

		public static function purgeDownloads() {
			$tTempPath = QPATH_APP . 'downloaded';

			foreach(glob($tTempPath . '/*', GLOB_MARK|GLOB_NOSORT) as $tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				unlink($tFilename);
			}
		}

		public static function purgeTemp() {
			$tTempPath = QPATH_APP . 'temp';

			foreach(glob($tTempPath . '/*', GLOB_MARK|GLOB_NOSORT) as $tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				unlink($tFilename);
			}
		}
	}

?>