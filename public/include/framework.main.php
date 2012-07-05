<?php

	/**
	* Framework manager
	*
	* @package Scabbia
	* @subpackage Core
	*
	* @todo download garbage collector, caching w/ aging
	* @todo global event-based garbage collector
	*/
	class framework {
		/**
		* @ignore
		*/
		public static $includePaths = array();
		/**
		* @ignore
		*/
		public static $downloadUrls = array();
		/**
		* @ignore
		*/
		public static $development;
		/**
		* @ignore
		*/
		public static $debug;
		/**
		* @ignore
		*/
		public static $siteroot;
		/**
		* @ignore
		*/
		public static $applicationPath;
		/**
		* @ignore
		*/
		public static $socket;
		/**
		* @ignore
		*/
		public static $directCall;

		/**
		* Initializes the framework manager.
		*/
		public static function init() {
			$tApplicationDir = isset($GLOBALS['applicationDir']) ? $GLOBALS['applicationDir'] : 'application';

			self::$applicationPath = QPATH_CORE . $tApplicationDir . DIRECTORY_SEPARATOR;
			self::$development = (isset($GLOBALS['development']) && $GLOBALS['development'] !== false);

			if(isset($_SERVER['SERVER_NAME'])) {
				self::$socket = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
			}
			else {
				self::$socket = 'localhost:80';
			}
		}

		/**
		* Translates the given path to real path.
		*
		* Example:
		* <code>
		* echo framework::translatePath('{core}bootconfig.php');
		* echo framework::translatePath('{app}config/framework.xml.php');
		* </code>
		*
		* @param string $uPath path
		*/
		public static function translatePath($uPath) {
			if(substr($uPath, 0, 6) == '{core}') {
				return QPATH_CORE . substr($uPath, 6);
			}

			if(substr($uPath, 0, 5) == '{app}') {
				return self::$applicationPath . substr($uPath, 5);
			}

			return $uPath;
		}

		/**
		* Compares if current php version is newer than the specified version.
		*
		* @param string $uVersion version
		*/
		public static function phpVersion($uVersion) {
			return version_compare(PHP_VERSION, $uVersion, '>=');
		}

		/**
		* Compares if current framework version is newer than the specified version.
		*
		* @param string $uVersion version
		*/
		public static function version($uVersion) {
			return version_compare(SCABBIA_VERSION, $uVersion, '>=');
		}

		/**
		* Loads the framework.
		*/
		public static function load() {
			self::$debug = (bool)config::get('/options/debug/@value', '0');
			self::$siteroot = config::get('/options/siteroot/@value', '');
			self::$directCall = !COMPILED;

			if(strlen(self::$siteroot) <= 1) {
				$tLen = strlen($_SERVER['DOCUMENT_ROOT']);
				if(substr(QPATH_CORE, 0, $tLen) == $_SERVER['DOCUMENT_ROOT']) {
					self::$siteroot = strtr(substr(QPATH_CORE, $tLen), DIRECTORY_SEPARATOR, '/');
				}
			}
			self::$siteroot = rtrim(self::$siteroot, '/');

			// extensions
			extensions::init();

			if(!COMPILED) {
				// downloads
				$tDownloads = config::get('/downloadList', array());

				foreach($tDownloads as &$tDownload) {
					self::$downloadUrls[$tDownload['@filename']] = $tDownload['@url'];
				}

				self::downloadFiles();

				// includes
				$tIncludes = config::get('/includeList', array());

				foreach($tIncludes as &$tInclude) {
					self::$includePaths[] = self::translatePath($tInclude['@path']);
				}

				self::includeFilesFromConfig();
			}
		}

		/**
		* Global output handler of framework.
		*/
		public static function output($uValue, $uSecond) {
			$tParms = array(
				'content' => &$uValue
			);

			Events::invoke('output', $tParms);

			if(OUTPUT_MULTIBYTE) {
				$tParms['content'] = mb_output_handler($tParms['content'], $uSecond); // PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_END
			}

			if(OUTPUT_GZIP && !PHP_SAPI_CLI && config::get('/options/gzip/@value', '1') != '0') {
				$tParms['content'] = ob_gzhandler($tParms['content'], $uSecond); // PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_END
			}

			return $tParms['content'];
		}

		/**
		* Boots the framework.
		*/
		public static function run() {
			ob_start('framework::output');
			ob_implicit_flush(false);

			if(!COMPILED) {
				if(self::phpVersion('5.3.6')) {
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
					$tParameters = array_keys($_GET);
				}

				if(self::$directCall) {
					if(self::$development && count($tParameters) > 0) {
						if($tParameters[0] == 'build') {
							self::build('index.php', !(count($tParameters) >= 2 && $tParameters[1] == 'pseudo'));
							self::purgeFolder(self::$applicationPath . 'writable/sessions');
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

		/**
		* Downloads the files from external sources.
		*
		* @uses downloadFile()
		*/
		public static function downloadFiles() {
			foreach(self::$downloadUrls as $tFilename => &$tUrl) {
				self::downloadFile($tFilename, $tUrl);
			}
		}

		/**
		* Download a file from external source.
		*
		* @param string $uFile filename
		* @param string $uUrl download source
		*/
		public static function downloadFile($uFile, $uUrl) {
			$tFilePath = self::$applicationPath . 'writable/downloaded/' . $uFile;
			if(file_exists($tFilePath)) {
				return false;
			}

			$tHandle = fopen($tFilePath, 'w');
			$tContent = file_get_contents($uUrl);
			fwrite($tHandle, $tContent);
			fclose($tHandle);

			return true;
		}

		/**
		* Includes the files from local sources.
		* @ignore
		*/
		private static function includeFilesFromConfig() {
			foreach(self::$includePaths as &$tPath) {
				foreach(glob3($tPath, false) as $tFilename) {
					if(substr($tFilename, -1) == '/') {
						continue;
					}

					require($tFilename);
				}
			}
		}
		
		/**
		* Prints the included files from local sources.
		*
		* @uses printFiles()
		* @ignore
		*/
		private static function printIncludeFilesFromConfig() {
			foreach(self::$includePaths as &$tPath) {
				self::printFiles(glob3($tPath, false));
			}
		}

		/**
		* Prints the specified files.
		*
		* @param array $uArray array of files
		* @uses printFile()
		*/
		public static function printFiles($uArray) {
			foreach($uArray as &$tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				self::printFile($tFilename);
			}
		}

		/**
		* Prints the specified file.
		*
		* @param string $uFile file
		*/
		public static function printFile($uFile) {
			$tContent = php_strip_whitespace($uFile);

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

		/**
		* Builds the framework in order to create a single compiled file.
		*
		* @param string $uFilename target file
		* @param bool $uPseudo pseudo status
		*/
		public static function build($uFilename, $uPseudo = true) {
			ob_start();
			ob_implicit_flush(false);

			if(self::$development && !$uPseudo) {
				$tPath = QPATH_CORE . 'framework' . QEXT_PHP;
				echo '<', '?php
	require(', var_export($tPath), ');
	extensions::run();
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
				echo php_strip_whitespace(QPATH_CORE . 'bootconfig' . QEXT_PHP);
				echo php_strip_whitespace(QPATH_CORE . 'include/config.main' . QEXT_PHP);
				echo php_strip_whitespace(QPATH_CORE . 'include/events.main' . QEXT_PHP);
				echo php_strip_whitespace(QPATH_CORE . 'include/framework.main' . QEXT_PHP);
				echo php_strip_whitespace(QPATH_CORE . 'include/extensions.main' . QEXT_PHP);

				echo '<', '?php framework::init(); config::set(', config::export(), '); framework::load(); ?', '>';

				self::printIncludeFilesFromConfig();

				echo '<', '?php extensions::load(); framework::run(); extensions::run(); ?', '>';
				/* END   */
			}

			$tContents = ob_get_contents();
			ob_end_clean();

			$tOutput = fopen($uFilename, 'w') or exit('Unable to write to ' . $uFilename);
			fwrite($tOutput, $tContents);
			fclose($tOutput);
		}

		/**
		* Purges specified path.
		*
		* @param string $uPath path
		*/
		public static function purgeFolder($uPath) {
			foreach(glob3($uPath . '/*', true) as $tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				unlink($tFilename);
			}
		}
	}

?>
