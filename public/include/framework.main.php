<?php

	// TODO: download garbage collector
	// TODO: global event-based garbage collector
	// TODO: download caching w/ aging

	/**
	* Base framework functions
	*
	* @package Scabbia
	* @subpackage Core
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
		* Indicates framework is running in production, development or debug mode.
		*/
		public static $development;
		/**
		* Stores relative path of framework root.
		*/
		public static $siteroot;
		/**
		* Stores relative path of running application.
		*/
		public static $applicationPath;
		/**
		* Stores active socket information.
		*/
		public static $socket;
		/**
		* @ignore
		*/
		public static $directCall;

		/**
		* @ignore
		*/
		public static function init() {
			$tApplicationDir = isset($GLOBALS['applicationDir']) ? $GLOBALS['applicationDir'] : 'application';

			self::$applicationPath = QPATH_CORE . $tApplicationDir . '/';
			self::$development = isset($GLOBALS['development']) ? $GLOBALS['development'] : 0;

			if(isset($_SERVER['SERVER_NAME'])) {
				self::$socket = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
			}
			else {
				self::$socket = 'localhost:80';
			}
		}

		/**
		* Translates given framework-relative path to physical path.
		*
		* @param string $uPath the framework-relative path
		* @return string translated physical path
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

		public static function writablePath($uFile) {
			$tPathConcat = self::$applicationPath . 'writable/' . $uFile;
			$tPathDirectory = pathinfo($tPathConcat, PATHINFO_DIRNAME);

			if(!is_dir($tPathDirectory)) {
				mkdir($tPathDirectory, 0777, true);
			}

			return $tPathConcat;
		}

		/**
		* Checks the given php version is greater than running one.
		*
		* @param string $uVersion php version
		* @return bool running php version is greater than parameter.
		*/
		public static function phpVersion($uVersion) {
			return version_compare(PHP_VERSION, $uVersion, '>=');
		}

		/**
		* Checks the given framework version is greater than running one.
		*
		* @param string $uVersion framework version
		* @return bool running framework version is greater than parameter.
		*/
		public static function version($uVersion) {
			return version_compare(SCABBIA_VERSION, $uVersion, '>=');
		}

		/**
		* @ignore
		*/
		public static function load() {
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
		* @ignore
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
		* @ignore
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
					if(self::$development >= 1 && count($tParameters) > 0) {
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
		* An utility function which helps functions to get parameters in array.
		*
		* @return array array of parameters
		*/
		public static function getArgs() {
			$uArgs = func_get_args();

			if(self::phpVersion('5.3.6')) {
				$tBacktrace = debug_backtrace();
			}
			else {
				$tBacktrace = debug_backtrace(false);
			}

			if(count($tBacktrace) < 2) {
				return null;
			}

			$tTargetArgs = $tBacktrace[1]['args'];

			if(count($tTargetArgs) == 1 && is_array($tTargetArgs[0])) {
				$tTargetArgs = $tTargetArgs[0];
			}
			else {
				$tNewArray = array();
				for($i = 0, $tMax = count($tTargetArgs), $tArgsMax = count($uArgs); $i < $tMax && $i < $tArgsMax; $i++) {
					$tNewArray[$uArgs[$i]] = array_shift($tTargetArgs);
				}

				$tTargetArgs = array_merge($tNewArray, $tTargetArgs);
			}

			return $tTargetArgs;
		}

		/**
		* @ignore
		*/
		public static function downloadFiles() {
			foreach(self::$downloadUrls as $tFilename => &$tUrl) {
				self::downloadFile($tFilename, $tUrl);
			}
		}

		/**
		* Downloads given file into framework's download directory.
		*
		* @param $uFile string filename in destination
		* @param $uUrl string url of source
		*/
		public static function downloadFile($uFile, $uUrl) {
			$tFilePath = self::writablePath('downloaded/' . $uFile);
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
		* @ignore
		*/
		private static function includeFilesFromConfig() {
			foreach(self::$includePaths as &$tPath) {
				$tFiles = glob3($tPath, false);
				if($tFiles !== false) {
					foreach($tFiles as $tFilename) {
						if(substr($tFilename, -1) == '/') {
							continue;
						}

						require($tFilename);
					}
				}
			}
		}

		/**
		* @ignore
		*/
		private static function printIncludeFilesFromConfig() {
			foreach(self::$includePaths as &$tPath) {
				self::printFiles(glob3($tPath, false));
			}
		}

		/**
		* @ignore
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
		* Outputs a php file source to view.
		*
		* @param $uFile string path of source file
		*/
		public static function printFile($uFile, $uReturnContents = false) {
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

			for(;$tOpenTags > 0;$tOpenTags--) {
				$tContent .= ' ?' . '>';
			}

			if($uReturnContents) {
				return $tContent;
			}

			echo $tContent;
		}

		/**
		* Builds a framework compilation.
		*
		* @param $uFilename string output file
		* @param $uPseudo bool wheater file is an pseudo compilation or not
		*/
		public static function build($uFilename, $uPseudo = true) {
			ob_start();
			ob_implicit_flush(false);

			if(self::$development >= 1 && !$uPseudo) {
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
		* Purges the files in given directory.
		*
		* @param $uFolder string destination directory
		*/
		public static function purgeFolder($uFolder) {
			foreach(glob3($uFolder . '/*', true) as $tFilename) {
				if(substr($tFilename, -1) == '/') {
					continue;
				}

				unlink($tFilename);
			}
		}
	}

?>
