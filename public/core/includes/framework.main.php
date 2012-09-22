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
		public static $error = null;

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
			// siteroot
			self::$siteroot = config::get('/options/siteroot/@value', '');

			if(strlen(self::$siteroot) <= 1) {
				$tDocumentRoot = strtr($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR, '/');
				$tLen = strlen($tDocumentRoot);
				if(substr(QPATH_BASE, 0, $tLen) == $tDocumentRoot) {
					self::$siteroot = substr(QPATH_BASE, $tLen);
				}
			}
			self::$siteroot = rtrim(self::$siteroot, '/');

			// extensions
			extensions::loadConfig();

			if(!COMPILED) {
				// downloads
				$tDownloads = config::get('/downloadList', array());

				foreach(self::$downloadUrls as $tFilename => &$tUrl) {
					self::$downloadUrls[$tDownload['@filename']] = $tDownload['@url'];
					self::downloadFile($tDownload['@filename'], $tDownload['@url']);
				}

				// includes
				$tIncludes = config::get('/includeList', array());

				foreach($tIncludes as &$tInclude) {
					$tIncludePath = self::translatePath($tInclude['@path']);
					self::$includePaths[] = $tIncludePath;

					$tFiles = glob3($tIncludePath, false);
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
		public static function end($uLevel = 0, $uErrorMessage = null) {
			self::$error = array($uLevel, $uErrorMessage);
			ob_end_flush();

			exit($uLevel);
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
		public static function &output($uValue, $uSecond) {
			$tParms = array(
				'error' => &self::$error,
				'content' => &$uValue
			);

			events::invoke('output', $tParms);

			//! check invoke order
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
		* Returns a php file source to view.
		*
		* @param $uInput string path of source file
		*/
		public static function printFile($uInput, $uOnlyContent = true) {
			$tDocComments = array();
			$tReturn = '';
			$tLastToken = -1;
			$tOpenStack = array();

			foreach(token_get_all($uInput) as $tToken) {
				if(is_array($tToken)) {
					$tTokenId = $tToken[0];
					$tTokenContent = $tToken[1];
				}
				else {
					$tTokenId = null;
					$tTokenContent = $tToken;
				}

				// $tReturn .= PHP_EOL . token_name($tTokenId) . PHP_EOL;
				switch($tTokenId) {
				case T_OPEN_TAG:
					$tReturn .= '<' . '?php ';
					array_push($tOpenStack, $tTokenId);
					break;

				case T_OPEN_TAG_WITH_ECHO:
					$tReturn .= '<' . '?php echo ';
					array_push($tOpenStack, $tTokenId);
					break;

				case T_CLOSE_TAG:
					$tLastOpen = array_pop($tOpenStack);

					if($tLastOpen == T_OPEN_TAG_WITH_ECHO) {
						$tReturn .= '; ';
					}
					else if($tLastToken != T_WHITESPACE) {
						$tReturn .= ' ';
					}

					$tReturn .= '?' . '>';
					break;

				case T_COMMENT:
				case T_DOC_COMMENT:
					if(substr($tTokenContent, 0, 3) == '/**') {
						$tCommentContent = substr($tTokenContent, 2, strlen($tTokenContent) - 4);

						foreach(explode("\n", $tCommentContent) as $tLine) {
							$tLineContent = ltrim($tLine, "\t ");

							if(substr($tLineContent, 0, 3) == '* @') {
								$tLineContents = explode(' ', substr($tLineContent, 3), 2);
								if(count($tLineContents) < 2) {
									continue;
								}

								if(!isset($tDocComments[$tLineContents[0]])) {
									$tDocComments[$tLineContents[0]] = array();
								}

								$tDocComments[$tLineContents[0]][] = $tLineContents[1];
							}
						}
					}
					break;

				case T_WHITESPACE:
					if($tLastToken != T_WHITESPACE &&
						$tLastToken != T_OPEN_TAG &&
						$tLastToken != T_OPEN_TAG_WITH_ECHO &&
						$tLastToken != T_COMMENT &&
						$tLastToken != T_DOC_COMMENT
					) {
						$tReturn .= ' ';
					}
					break;

				case null:
					$tReturn .= $tTokenContent;
					if($tLastToken == T_END_HEREDOC) {
						$tReturn .= "\n";
						$tTokenId = T_WHITESPACE;
					}
					break;

				default:
					$tReturn .= $tTokenContent;
					break;
				}

				$tLastToken = $tTokenId;
			}

			while(count($tOpenStack) > 0) {
				$tLastOpen = array_pop($tOpenStack);
				if($tLastOpen == T_OPEN_TAG_WITH_ECHO) {
					$tReturn .= '; ';
				}
				else if($tLastToken != T_WHITESPACE) {
					$tReturn .= ' ';
				}

				$tReturn .= '?' . '>';
			}

			if(!$uOnlyContent) {
				$tArray = array(&$tReturn, &$tDocComments);
				return $tArray;
			}

			return $tReturn;
		}
	}

?>
