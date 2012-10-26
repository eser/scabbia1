<?php

	// TODO: download garbage collector
	// TODO: global event-based garbage collector
	// TODO: download caching w/ aging

	define('GLOB_NONE', 0);
	define('GLOB_RECURSIVE', 1);
	define('GLOB_FILES', 2);
	define('GLOB_DIRECTORIES', 4);
	define('GLOB_JUSTNAMES', 8);

	/**
	* Base framework functions
	*
	* @package Scabbia
	* @subpackage Core
	*
	* @todo serialize/unserialize data (example: resources)
	*/
	class framework {
		/**
		* Indicates framework is running in production, development or debug mode.
		*/
		public static $development = 0;
		/**
		* Stores active module information.
		*/
		public static $module = null;
		/**
		* Stores relative path of framework root.
		*/
		public static $siteroot = null;
		/**
		* Stores relative path of running application.
		*/
		public static $applicationPath = null;
		/**
		* @ignore
	 	*/
		public static $milestones = array();
		/**
		* Stores all available endpoints.
	 	*/
		public static $endpoints = array();
		/**
		* Stores active endpoint information.
		*/
		public static $endpoint = null;
		/**
		* Stores active ssl socket information.
		*/
		public static $issecure;
		/**
		* @ignore
		*/
		public static $error = null;

		/**
		* @ignore
		*/
		public static function load($uRunExtensions = true) {
			self::$milestones['begin'] = microtime(true);

			// endpoints
			if(count(self::$endpoints) > 0) {
				foreach(self::$endpoints as &$tEndpoint) {
					$tParsed = parse_url($tEndpoint);
					if(!isset($tParsed['port'])) {
						$tParsed['port'] = ($tParsed['scheme'] == 'https') ? 443 : 80;
					}

					if($_SERVER['SERVER_NAME'] == $tParsed['host'] && $_SERVER['SERVER_PORT'] == $tParsed['port']) {
						self::$endpoint = &$tEndpoint;
						self::$issecure = ($tParsed['scheme'] == 'https');
						break;
					}
				}

				if(is_null(self::$endpoint)) {
					throw new Exception('no endpoints match.');
				}
			}

			// application path
			if(is_null(self::$applicationPath)) {
				self::$applicationPath = QPATH_BASE . 'application/';
			}
			self::$milestones['endpoints'] = microtime(true);

			// load config
			if(!COMPILED) {
				config::load();
				self::$milestones['configLoad'] = microtime(true);

				// downloads
				foreach(config::get(config::MAIN, '/downloadList', array()) as $tUrl) {
					self::downloadFile($tUrl['filename'], $tUrl['url']);
				}
				self::$milestones['downloads'] = microtime(true);

				// events::load();
				extensions::load();
				self::$milestones['extensions'] = microtime(true);
			}

			// siteroot
			if(is_null(self::$siteroot)) {
				self::$siteroot = config::get(config::MAIN, '/options/siteroot', pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME));
			}
			self::$milestones['siteRoot'] = microtime(true);

			// extensions
			extensions::loadExtensions();

			if(!COMPILED) {
				// includes
				foreach(config::get(config::MAIN, '/includeList', array()) as $tInclude) {
					$tIncludePath = self::translatePath($tInclude);

					$tFiles = self::glob($tIncludePath, GLOB_FILES);
					if($tFiles !== false) {
						foreach($tFiles as $tFilename) {
							if(substr($tFilename, -1) == '/') {
								continue;
							}

							include($tFilename);
						}
					}
				}
				self::$milestones['includesLoad'] = microtime(true);
			}

			// output handling
			ob_start('framework::output');
			ob_implicit_flush(false);

			// run extensions
			if($uRunExtensions) {
				events::invoke('run', array());
			}
			self::$milestones['extensionsRun'] = microtime(true);
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

		/**
		 * @ignore
		 */
		public static function writablePath($uFile) {
			$tPathConcat = self::$applicationPath . 'writable/' . $uFile;
			$tPathDirectory = pathinfo($tPathConcat, PATHINFO_DIRNAME);

			if(!is_dir($tPathDirectory)) {
				if(PHP_SAFEMODE) {
					throw new Exception($tPathDirectory . ' does not exists.');
				}

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
			if(OUTPUT_NOHANDLER) {
				$tParms['content'] = mb_output_handler($tParms['content'], $uSecond); // PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_END
			}

			if(OUTPUT_GZIP && !PHP_SAPI_CLI && config::get(config::MAIN, '/options/gzip', '1') != '0') {
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
			$tUrlHandle = fopen($uUrl, 'rb', false);
			if($tUrlHandle === false) {
				return false;
			}

			$tHandle = fopen(self::writablePath('downloaded/' . $uFile), 'wb', false);
			if($tHandle === false) {
				fclose($tUrlHandle);
				return false;
			}

			if(flock($tHandle, LOCK_EX) === false) {
				fclose($tHandle);
				fclose($tUrlHandle);
				return false;
			}

			stream_copy_to_stream($tUrlHandle, $tHandle);
			fflush($tHandle);
			flock($tHandle, LOCK_UN);
			fclose($tHandle);

			fclose($tUrlHandle);

			return true;
		}

		/**
		* Returns a php file source to view.
		*
		* @param $uInput string path of source file
		*/
		public static function &glob($uPattern, $uOptions = GLOB_FILES, $uRecursivePath = '', &$uArray = array()) {
			$tPath = strtr(pathinfo($uPattern, PATHINFO_DIRNAME), DIRECTORY_SEPARATOR, '/');
			$tPattern = pathinfo($uPattern, PATHINFO_BASENAME);

			try {
				$tDir = new DirectoryIterator($tPath);

				foreach($tDir as $tFile) {
					$tFileName = $tFile->getFilename();

					if($tFileName[0] == '.') { // $tFile->isDot()
						continue;
					}

					if($tFile->isDir()) {
						$tDirectory = $uRecursivePath . $tFileName . '/';

						if(($uOptions & GLOB_DIRECTORIES) > 0) {
							$uArray[] = (($uOptions & GLOB_JUSTNAMES) > 0) ? $tDirectory : $tPath . '/' . $tDirectory;
						}

						if(($uOptions & GLOB_RECURSIVE) > 0) {
							self::glob(
								$tPath . '/' . $tDirectory . $tPattern,
								$uOptions,
								$tDirectory,
								$uArray
							);
						}

						continue;
					}

					if(($uOptions & GLOB_FILES) > 0 && $tFile->isFile()) {
						$tFile2 = $uRecursivePath . $tFileName;

						if(fnmatch($uPattern, $tPath . '/' . $tFile2)) {
							$uArray[] = (($uOptions & GLOB_JUSTNAMES) > 0) ? $tFile2 : $tPath . '/' . $tFile2;
						}

						continue;
					}
				}

				return $uArray;
			}
			catch(Exception $tException) {
				// echo $tException->getMessage();
			}

			$uArray = false;
			return $uArray;
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
