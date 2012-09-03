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
		* Stores active socket information.
		*/
		public static $issecure;
		/**
		* @ignore
		*/
		public static $runExtensions;

		/**
		* @ignore
		*/
		public static function init() {
			$tApplications = config::get('/applicationList');

			if(defined('APPLICATION')) {
				foreach($tApplications as &$tApplication) {
					if(constant('APPLICATION') == $tApplication['@name']) {
						self::$socket = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
						self::$issecure = false;
						$tPickedApplication = &$tApplication;
						break;
					}
				}
			}
			else {
				foreach($tApplications as &$tApplication) {
					if(isset($tApplication['@host']) && $tApplication['@host'] != $_SERVER['SERVER_NAME']) {
						continue;
					}

					if(isset($tApplication['@secureport']) && $tApplication['@secureport'] == $_SERVER['SERVER_PORT']) {
						self::$socket = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
						self::$issecure = true;
						$tPickedApplication = &$tApplication;
						break;
					}

					if(isset($tApplication['@port']) && $tApplication['@port'] == $_SERVER['SERVER_PORT']) {
						self::$socket = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
						self::$issecure = false;
						$tPickedApplication = &$tApplication;
						break;
					}

					if(isset($tApplication['bindList'])) {
						foreach($tApplication['bindList'] as $tBind) {
							if(isset($tBind['@host']) && $tBind['@host'] != $_SERVER['SERVER_NAME']) {
								continue;
							}

							if($tBind['@port'] == $_SERVER['SERVER_PORT']) {
								self::$socket = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
								self::$issecure = (isset($tBind['@secure']) && (bool)$tBind['@secure']);
								$tPickedApplication = &$tApplication;
								break;
							}
						}
					}
				}
			}

			if(!isset($tPickedApplication)) {
				exit('why?');
			}

			self::$applicationPath = self::translatePath($tPickedApplication['@path']);
			self::$development = isset($tPickedApplication['@development']) ? intval($tPickedApplication['@development']) : 0;
			if(!defined('EXTENSIONS')) {
				self::$runExtensions = (!isset($tPickedApplication['@runExtensions']) || (bool)$tPickedApplication['@runExtensions']);
			}
			else {
				self::$runExtensions = constant('EXTENSIONS');
			}
		}

		/**
		* @ignore
		*/
		public static function load() {
			self::$siteroot = config::get('/options/siteroot/@value', '');

			if(strlen(self::$siteroot) <= 1) {
				$tLen = strlen($_SERVER['DOCUMENT_ROOT']);
				if(substr(QPATH_BASE, 0, $tLen) == $_SERVER['DOCUMENT_ROOT']) {
					self::$siteroot = strtr(substr(QPATH_BASE, $tLen), DIRECTORY_SEPARATOR, '/');
				}
			}
			self::$siteroot = rtrim(self::$siteroot, '/');

			// extensions
			extensions::loadConfig();

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
		public static function run() {
			ob_start('framework::output');
			ob_implicit_flush(false);

			// if(!COMPILED) {
			// 	$tDirectCall = true;
			//
			// 	if(self::phpVersion('5.3.6')) {
			// 		$tBacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			// 	}
			// 	else {
			// 		$tBacktrace = debug_backtrace(false);
			// 	}
            //
			// 	foreach($tBacktrace as &$tValue) {
			// 		if(isset($tValue['function']) && ($tValue['function'] == 'include' || $tValue['function'] == 'require')) {
			// 			$tDirectCall = false;
			// 		}
			// 	}
            //
			// 	if(PHP_SAPI_CLI) {
			// 		$tParameters = array_slice($_SERVER['argv'], 1);
			// 	}
			// 	else {
			// 		$tParameters = array_keys($_GET);
			// 	}
			// }
		}

		/**
		* @ignore
		*/
		public static function end($uError = 0) {
			ob_end_flush();
			exit($uError);
		}

		/**
		* Translates given framework-relative path to physical path.
		*
		* @param string $uPath the framework-relative path
		* @return string translated physical path
		*/
		public static function translatePath($uPath, $uBasePath = null) {
			if(substr($uPath, 0, 6) == '{base}') {
				return QPATH_BASE . substr($uPath, 6);
			}

			if(substr($uPath, 0, 6) == '{core}') {
				return QPATH_CORE . substr($uPath, 6);
			}

			if(substr($uPath, 0, 5) == '{app}') {
				return self::$applicationPath . substr($uPath, 5);
			}

			if(is_null($uBasePath)) {
				return $uPath;
			}

			return $uBasePath . $uPath;
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
		* An utility function which helps functions to get parameters in array.
		*
		* @return array array of parameters
		*/
		// public static function getArgs() {
		// 	$uArgs = func_get_args();
        //
		// 	if(self::phpVersion('5.3.6')) {
		// 		$tBacktrace = debug_backtrace();
		// 	}
		// 	else {
		// 		$tBacktrace = debug_backtrace(false);
		// 	}
        //
		// 	if(count($tBacktrace) < 2) {
		// 		return null;
		// 	}
        //
		// 	$tTargetArgs = $tBacktrace[1]['args'];
        //
		// 	if(count($tTargetArgs) == 1 && is_array($tTargetArgs[0])) {
		// 		$tTargetArgs = $tTargetArgs[0];
		// 	}
		// 	else {
		// 		$tNewArray = array();
		// 		for($i = 0, $tMax = count($tTargetArgs), $tArgsMax = count($uArgs); $i < $tMax && $i < $tArgsMax; $i++) {
		// 			$tNewArray[$uArgs[$i]] = array_shift($tTargetArgs);
		// 		}
        //
		// 		$tTargetArgs = array_merge($tNewArray, $tTargetArgs);
		// 	}
        //
		// 	return $tTargetArgs;
		// }

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
		public static function printIncludeFilesFromConfig() {
			foreach(self::$includePaths as &$tPath) {
				$tFiles = glob3($tPath, false, true);
				if($tFiles == false) {
					continue;
				}
				self::printFiles($tFiles);
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
	}

?>
