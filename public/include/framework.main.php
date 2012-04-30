<?php

	class Framework {
		public static $includePaths = array();
		public static $downloadUrls = array();
		public static $development;
		public static $debug;
		public static $siteroot;
		public static $directCall;
		
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
			self::$development = Config::$development;
			self::$debug = (bool)Config::get('/options/debug/@value', '0');
			self::$siteroot = Config::get('/options/siteroot/@value', '');
			self::$directCall = !COMPILED;

			if(strlen(self::$siteroot) <= 1) {
				$tLen = strlen($_SERVER['DOCUMENT_ROOT']);
				if(substr(QPATH_CORE, 0, $tLen) == $_SERVER['DOCUMENT_ROOT']) {
					self::$siteroot = strtr(substr(QPATH_CORE, $tLen), DIRECTORY_SEPARATOR, '/');
				}
			}
			self::$siteroot = rtrim(self::$siteroot, '/');

			// extensions
			Extensions::init();

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

		public static function output($uValue, $uSecond) {
			$tParms = array(
				'content' => &$uValue
			);

			Events::invoke('output', $tParms);

			if(OUTPUT_MULTIBYTE) {
				$tParms['content'] = mb_output_handler($tParms['content'], $uSecond); // PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_END
			}

			if(OUTPUT_GZIP && !PHP_SAPI_CLI && Config::get('/options/gzip/@value', '1') != '0') {
				$tParms['content'] = ob_gzhandler($tParms['content'], $uSecond); // PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_END
			}

			return $tParms['content'];
		}

		public static function run() {
			ob_start('Framework::output');
			ob_implicit_flush(false);

			if(!COMPILED) {
				if(version_compare(PHP_VERSION, '5.3.6', '>=')) {
					$tBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				}
				else {
					$tBacktrace = debug_backtrace(false);
				}

				foreach($tBacktrace as &$tValue) {
					if(isset($tValue['function']) && ($tValue['function'] == 'include' || $tValue['function'] == 'require')) {
						self::$directCall = false;
					}
				}

				if(PHP_SAPI_CLI) {
					$tParameters = array_slice($_SERVER['argv'], 1);
				}
				else {
					$tParameters = $_GET;
				}

				if(self::$directCall) {
					if(self::$development && count($tParameters) > 0) {
						if($tParameters[0] == 'build') {
							self::build('index.php', !(count($tParameters) >= 2 && $tParameters[1] == 'pseudo'));
							self::purgeFolder(QPATH_APP . 'writable/sessions');
							// self::purgeFolder(QPATH_APP . 'writable/datasetCache');
							// self::purgeFolder(QPATH_APP . 'writable/mediaCache');
							// self::purgeFolder(QPATH_APP . 'writable/downloaded');
							// self::purgeFolder(QPATH_APP . 'writable/compiledViews'));
							// self::purgeFolder(QPATH_APP . 'writable/logs');

							echo 'build done.';
							return;
						}
					}
					
					exit('why?');
				}
			}
		}

		public static function downloadFiles() {
			foreach(self::$downloadUrls as $tFilename => &$tUrl) {
				self::downloadFile($tFilename, $tUrl);
			}
		}

		public static function downloadFile($uFile, $uUrl) {
			$tFilePath = QPATH_APP . 'writable/downloaded/' . $uFile;
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
				foreach(glob2($tPath) as $tFilename) {
					if(substr($tFilename, -1) == '/') {
						continue;
					}

					require($tFilename);
				}
			}
		}
		
		private static function printIncludeFilesFromConfig() {
			foreach(self::$includePaths as &$tPath) {
				self::printFiles(glob2($tPath));
			}
		}

		public static function printFiles($uArray) {
			foreach($uArray as &$tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				$tContent = php_strip_whitespace($tFilename);

				$tOpenTags = 0;
				foreach(token_get_all($tContent) as $tToken) {
					if($tToken[0] == T_OPEN_TAG || $tToken[0] == T_OPEN_TAG_WITH_ECHO) {
						$tOpenTags++;
					}
					else if($tToken[0] == T_CLOSE_TAG) {
						$tOpenTags--;
					}
				}

				echo $tContent;
				for(;$tOpenTags > 0;$tOpenTags--) {
					echo ' ?', '>';
				}
			}
		}

		public static function build($uFilename, $uForce = true) {
			ob_start();
			ob_implicit_flush(false);

			if(self::$development && !$uForce) {
				echo '<', '?php
	require(\'', QPATH_CORE, 'framework', QEXT_PHP, '\');
	Extensions::run();
?', '>';
			}
			else {
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

				echo '<', '?php Config::set(', Config::export(), '); Framework::load(); ?', '>';

				self::printIncludeFilesFromConfig();

				echo '<', '?php Extensions::load(); Framework::run(); Extensions::run(); ?', '>';
				/* END   */
			}

			$tContents = ob_get_contents();
			ob_end_clean();

			$tOutput = fopen($uFilename, 'w') or exit('Unable to write to ' . $uFilename);
			fwrite($tOutput, $tContents);
			fclose($tOutput);
		}

		public static function purgeFolder($uFolder) {
			foreach(glob2($uFolder . '/*') as $tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				unlink($tFilename);
			}
		}
	}

?>