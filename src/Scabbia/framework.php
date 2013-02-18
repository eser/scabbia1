<?php

	namespace Scabbia;

	use Scabbia\config;
	use Scabbia\extensions;
	use Scabbia\events;

	// TODO: download garbage collector
	// TODO: global event-based garbage collector
	// TODO: download caching w/ aging

	/**
	 * Base framework functions
	 *
	 * @package Scabbia
	 * @subpackage Core
	 *
	 * @todo serialize/unserialize data (example: resources)
	 */
	class framework {
		const VERSION = '1.0';

		const GLOB_NONE = 0;
		const GLOB_RECURSIVE = 1;
		const GLOB_FILES = 2;
		const GLOB_DIRECTORIES = 4;
		const GLOB_JUSTNAMES = 8;

		/**
		 * Indicates the base directory which framework runs in.
		 */
		public static $basepath = null;
		/**
		 * Indicates the core directory which framework runs in.
		 */
		public static $corepath = null;
		/**
		 * Indicates framework is running in production, development or debug mode.
		 */
		public static $development = 0;
		/**
		 * Indicates framework is running in readonly mode or not.
		 */
		public static $readonly = false;
		/**
		 * Indicates framework is running in compiled mode or not.
		 */
		public static $compiled = false;
		/**
		 * @ignore
		 */
		public static $timestamp = false;
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
		 * @ignore
		 */
		public static $error = null;
		/**
		 * @ignore
		 */
		public static $regexpPresets = array(
			'num' => '[0-9]+',
			'num?' => '[0-9]*',
			'alnum' => '[a-zA-Z0-9]+',
			'alnum?' => '[a-zA-Z0-9]*',
			'any' => '[a-zA-Z0-9\.\-_%=]+', // ~
			'any?' => '[a-zA-Z0-9\.\-_%=]*',
			'all' => '.+',
			'all?' => '.*'
		);


		/**
		 * @ignore
		 */
		public static function load() {
			self::$timestamp = microtime(true);
			self::$milestones[] = array('begin', microtime(true));

			if(version_compare(PHP_VERSION, '5.3.0', '<') && ini_get('safe_mode')) {
				self::$readonly = true;
			}

			if(is_null(self::$basepath)) {
				self::$basepath = strtr(pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_DIRNAME), DIRECTORY_SEPARATOR, '/') . '/';
			}
			self::$corepath = strtr(pathinfo(__FILE__, PATHINFO_DIRNAME), DIRECTORY_SEPARATOR, '/') . '/';

			// Set error reporting occasions
			error_reporting(defined('E_STRICT') ? E_ALL | E_STRICT : E_ALL);
			// ini_set('display_errors', '1');
			// ini_set('log_errors', '0');
			// ini_set('error_log', framework::$basepath . 'error.log');

			// Include framework dependencies and load them
			require(framework::$corepath . 'patches.php');
			require(framework::$corepath . 'framework.php');
			require(framework::$corepath . 'config.php');
			require(framework::$corepath . 'events.php');
			require(framework::$corepath . 'extensions.php');

			// endpoints
			if(count(self::$endpoints) > 0) {
				foreach(self::$endpoints as $tEndpoint) {
					$tParsed = parse_url($tEndpoint);
					if(!isset($tParsed['port'])) {
						$tParsed['port'] = ($tParsed['scheme'] == 'https') ? 443 : 80;
					}

					if($_SERVER['SERVER_NAME'] == $tParsed['host'] && $_SERVER['SERVER_PORT'] == $tParsed['port']) {
						self::$endpoint = $tEndpoint;
						// self::$issecure = ($tParsed['scheme'] == 'https');
						break;
					}
				}

				if(is_null(self::$endpoint)) {
					throw new \Exception('no endpoints match.');
				}
			}

			self::$milestones[] = array('endpoints', microtime(true));

			if(!self::$readonly && is_null(self::$applicationPath)) {
				self::$applicationPath = framework::$basepath . '/application/';
			}

			if(!self::$compiled) {
				// load config
				config::$default = config::load();
				self::$milestones[] = array('configLoad', microtime(true));

				// download files
				foreach(config::get('/downloadList', array()) as $tUrl) {
					self::downloadFile($tUrl['filename'], $tUrl['url']);
				}
				self::$milestones[] = array('downloads', microtime(true));

				// load extensions
				extensions::$list = extensions::load();
				self::$milestones[] = array('extensions', microtime(true));
			}

			// siteroot
			if(is_null(self::$siteroot)) {
				self::$siteroot = config::get('/options/siteroot', pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME));
			}
			self::$milestones[] = array('siteRoot', microtime(true));

			// extensions and autoload
			// spl_autoload_register('Scabbia\\extensions::autoloader');
			extensions::loadExtensions();

			if(!self::$compiled) {
				// include files
				foreach(config::get('/includeList', array()) as $tInclude) {
					$tIncludePath = pathinfo(self::translatePath($tInclude));

					$tFiles = self::glob($tIncludePath['dirname'] . '/', $tIncludePath['basename'], self::GLOB_FILES);
					if($tFiles !== false) {
						foreach($tFiles as $tFilename) {
							//! todo require_once?
							include($tFilename);
						}
					}
				}
				self::$milestones[] = array('includesLoad', microtime(true));
			}

			// output handling
			ob_start('Scabbia\\framework::output');
			ob_implicit_flush(false);

			// run extensions
			$tParms = array();
			events::invoke('run', $tParms);
			self::$milestones[] = array('extensionsRun', microtime(true));
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
		 * @param null $uBasePath
		 *
		 * @return string translated physical path
		 */
		public static function translatePath($uPath, $uBasePath = null) {
			if(substr($uPath, 0, 6) == '{base}') {
				return framework::$basepath . substr($uPath, 6);
			}

			if(substr($uPath, 0, 6) == '{core}') {
				return framework::$corepath . substr($uPath, 6);
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
		public static function writablePath($uFile = '', $uCreateFolder = false) {
			$tPathConcat = self::$applicationPath . 'writable/' . $uFile;

			if($uCreateFolder) {
				$tPathDirectory = pathinfo($tPathConcat, PATHINFO_DIRNAME);

				if(!is_dir($tPathDirectory)) {
					if(self::$readonly) {
						throw new \Exception($tPathDirectory . ' does not exists.');
					}

					mkdir($tPathDirectory, 0777, true);
				}
			}

			return $tPathConcat;
		}

		/**
		 * Checks the given php version is greater than running one.
		 *
		 * @param string $uVersion php version
		 *
		 * @return bool running php version is greater than parameter.
		 */
		public static function phpVersion($uVersion) {
			return version_compare(PHP_VERSION, $uVersion, '>=');
		}

		/**
		 * Checks the given framework version is greater than running one.
		 *
		 * @param string $uVersion framework version
		 *
		 * @return bool running framework version is greater than parameter.
		 */
		public static function version($uVersion) {
			return version_compare(self::VERSION, $uVersion, '>=');
		}

		/**
		 * @ignore
		 */
		public static function output($uValue, $uSecond) {
			$tParms = array(
				'error' => &self::$error,
				'content' => &$uValue
			);

			events::invoke('output', $tParms);

			//! check invoke order
			if(ini_get('output_handler') == '') {
				$tParms['content'] = mb_output_handler($tParms['content'], $uSecond); // PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_END

				if(!ini_get('zlib.output_compression') && (PHP_SAPI != 'cli') && config::get('/options/gzip', '1') != '0') {
					$tParms['content'] = ob_gzhandler($tParms['content'], $uSecond); // PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_END
				}
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
		 *
		 * @return bool
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
		 * @param $uPath
		 * @param null $uFilter
		 * @param int $uOptions
		 * @param string $uRecursivePath
		 * @param array $uArray
		 *
		 * @internal param string $uInput path of source file
		 * @return array|bool
		 */
		public static function glob($uPath, $uFilter = null, $uOptions = self::GLOB_FILES, $uRecursivePath = '', &$uArray = array()) {
			$tPath = rtrim(strtr($uPath, DIRECTORY_SEPARATOR, '/'), '/') . '/';
			$tRecursivePath = $tPath . $uRecursivePath;

			try {
				$tDir = new \DirectoryIterator($tRecursivePath);

				foreach($tDir as $tFile) {
					$tFileName = $tFile->getFilename();

					if($tFileName[0] == '.') { // $tFile->isDot()
						continue;
					}

					if($tFile->isDir()) {
						$tDirectory = $uRecursivePath . $tFileName . '/';

						if(($uOptions & self::GLOB_DIRECTORIES) > 0) {
							$uArray[] = (($uOptions & self::GLOB_JUSTNAMES) > 0) ? $tDirectory : $tPath . $tDirectory;
						}

						if(($uOptions & self::GLOB_RECURSIVE) > 0) {
							self::glob(
								$tPath,
								$uFilter,
								$uOptions,
								$tDirectory,
								$uArray
							);
						}

						continue;
					}

					if(($uOptions & self::GLOB_FILES) > 0 && $tFile->isFile()) {
						if(is_null($uFilter) || fnmatch($uFilter, $tFileName)) {
							$uArray[] = (($uOptions & self::GLOB_JUSTNAMES) > 0) ? $uRecursivePath . $tFileName : $tRecursivePath . $tFileName;
						}

						continue;
					}
				}

				return $uArray;
			}
			catch(\Exception $tException) {
				// echo $tException->getMessage();
			}

			$uArray = false;

			return $uArray;
		}

		/**
		 * @ignore
		 *
		 * @param string $uPattern the pattern to search for, as a string
		 *
		 * @return string
		 */
		 private static function pregFormat($uPattern) {
			$tBuffer = array(array(false, ''));
			$tBrackets = 0;

			for($tPos = 0, $tLen = strlen($uPattern); $tPos < $tLen; $tPos++) {
				$tChar = substr($uPattern, $tPos, 1);

				if($tChar == '\\') {
				 	$tBuffer[$tBrackets][1] .= substr($uPattern, ++$tPos, 1);
				 	continue;
				}

				if($tChar == '(') {
					$tBuffer[++$tBrackets] = array(false, '');
					continue;
				}

				if($tBrackets > 0) {
					if($tChar == ':' && $tBuffer[$tBrackets][0] === false) {
						$tBuffer[$tBrackets][0] = $tBuffer[$tBrackets][1];
						$tBuffer[$tBrackets][1] = '';

						continue;
					}

					if($tChar == ')') {
						--$tBrackets;
						$tLast = array_pop($tBuffer);

						if($tLast[0] === false) {
							$tBuffer[$tBrackets][1] .= '(?:';
						}
						else {
							$tBuffer[$tBrackets][1] .= '(?P<' . $tLast[0] . '>';
						}

						if(array_key_exists($tLast[1], self::$regexpPresets)) {
							$tBuffer[$tBrackets][1] .= self::$regexpPresets[$tLast[1]] . ')';
						}
						else {
							$tBuffer[$tBrackets][1] .= $tLast[1] . ')';
						}

						continue;
					}
				}

				if($tChar == ')') {
					$tBuffer[$tBrackets][1] .= '\\)';
					continue;
				}

				$tBuffer[$tBrackets][1] .= $tChar;
			}

			while($tBrackets > 0) {
				--$tBrackets;
				$tLast = array_pop($tBuffer);
				$tBuffer[0][1] .= '\\(' . $tLast[1];
			}

			return $tBuffer[0][1];
		}

		/**
		 * Searches subject for a match to the regular expression given in pattern.
		 *
		 * @param string $uPattern the pattern to search for, as a string
		 * @param string $uSubject the input string
		 * @param string $uModifiers the PCRE modifiers
		 *
		 * @return array
		 */
		public static function pregMatch($uPattern, $uSubject, $uModifiers = '^') {
			$tPattern = self::pregFormat($uPattern);

			if(strpos($uModifiers, '^') === 0) {
				preg_match('#^' . $tPattern . '$#' . substr($uModifiers, 1), $uSubject, $tResult);
			}
			else {
				preg_match('#' . $tPattern . '#' . $uModifiers, $uSubject, $tResult);
			}

			// if(count($tResult) > 0) {
			//	return $tResult;
			// }
			//
			// return false;

			return $tResult;
		}

		/**
		 * Replaces subject with the matches of the regular expression given in pattern.
		 *
		 * @param string $uPattern the pattern to search for, as a string
		 * @param string $uReplacement the replacement string
		 * @param string $uSubject the string or an array with strings to replace
		 * @param string $uModifiers the PCRE modifiers
		 *
		 * @return array
		 */
		public static function pregReplace($uPattern, $uReplacement, $uSubject, $uModifiers = '^') {
			$tPattern = self::pregFormat($uPattern);

			if(strpos($uModifiers, '^') === 0) {
				$tResult = preg_replace('#^' . $tPattern . '$#' . substr($uModifiers, 1), $uReplacement, $uSubject, -1, $tCount);
			}
			else {
				$tResult = preg_replace('#' . $tPattern . '#' . $uModifiers, $uReplacement, $uSubject, -1, $tCount);
			}

			if($tCount > 0) {
				return $tResult;
			}

			return false;
		}

		/**
		 * Returns a php file source to view.
		 *
		 * @param $uInput string path of source file
		 * @param bool $uOnlyContent
		 *
		 * @return array|string
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
					else {
						if($tLastToken != T_WHITESPACE) {
							$tReturn .= ' ';
						}
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
				else {
					if($tLastToken != T_WHITESPACE) {
						$tReturn .= ' ';
					}
				}

				$tReturn .= '?' . '>';
			}

			if(!$uOnlyContent) {
				$tArray = array(&$tReturn, $tDocComments);

				return $tArray;
			}

			return $tReturn;
		}
	}

	?>
