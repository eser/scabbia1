<?php

	namespace Scabbia\Extensions\Io;

	/**
	 * IO Extension
	 *
	 * @package Scabbia
	 * @subpackage io
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class io {
		/**
		 * @ignore
		 */
		public static function getMimeType($uExtension, $uDefault = 'application/octet-stream') {
			switch(string::toLower($uExtension)) {
			case 'pdf':
				$tType = 'application/pdf';
				break;
			case 'exe':
				$tType = 'application/octet-stream';
				break;
			case 'dll':
				$tType = 'application/x-msdownload';
				break;
			case 'zip':
				$tType = 'application/zip';
				break;
			case 'rar':
				$tType = 'application/x-rar-compressed';
				break;
			case 'gz':
				$tType = 'application/x-gzip';
				break;
			case 'tar':
				$tType = 'application/x-tar';
				break;
			case 'deb':
				$tType = 'application/x-deb';
				break;
			case 'dmg':
				$tType = 'application/x-apple-diskimage';
				break;
			case 'csv':
				$tType = 'text/csv';
				break;
			case 'txt':
			case 'text':
			case 'log':
			case 'ini':
				$tType = 'text/plain';
				break;
			case 'rtf':
				$tType = 'text/rtf';
				break;
			case 'odt':
				$tType = 'application/vnd.oasis.opendocument.text';
				break;
			case 'eml':
				$tType = 'message/rfc822';
				break;
			case 'xml':
			case 'xsl':
				$tType = 'text/xml';
				break;
			case 'doc':
			case 'word':
				$tType = 'application/msword';
				break;
			case 'docx':
				$tType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
				break;
			case 'xls':
				$tType = 'application/vnd.ms-excel';
				break;
			case 'xl':
				$tType = 'application/excel';
				break;
			case 'xlsx':
				$tType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
				break;
			case 'ppt':
				$tType = 'application/vnd.ms-powerpoint';
				break;
			case 'bmp':
				$tType = 'image/x-ms-bmp';
				break;
			case 'gif':
				$tType = 'image/gif';
				break;
			case 'png':
				$tType = 'image/png';
				break;
			case 'jpeg':
			case 'jpe':
			case 'jpg':
				$tType = 'image/jpeg';
				break;
			case 'webp':
				$tType = 'image/webp';
				break;
			case 'tif':
			case 'tiff':
				$tType = 'image/tiff';
				break;
			case 'psd':
				$tType = 'image/vnd.adobe.photoshop';
				break;
			case 'mid':
			case 'midi':
				$tType = 'audio/midi';
				break;
			case 'mpga':
			case 'mp2':
			case 'mp3':
				$tType = 'audio/mpeg';
				break;
			case 'wav':
				$tType = 'audio/x-wav';
				break;
			case 'aac':
				$tType = 'audio/aac';
				break;
			case 'ogg':
				$tType = 'audio/ogg';
				break;
			case 'wma':
				$tType = 'audio/x-ms-wma';
				break;
			case 'mpeg':
			case 'mpg':
			case 'mpe':
				$tType = 'video/mpeg';
				break;
			case 'mp4':
				$tType = 'application/mp4';
				break;
			case 'qt':
			case 'mov':
				$tType = 'video/quicktime';
				break;
			case 'avi':
				$tType = 'video/x-msvideo';
				break;
			case 'wmv':
				$tType = 'video/x-ms-wmv';
				break;
			case 'swf':
				$tType = 'application/x-shockwave-flash';
				break;
			case 'flv':
				$tType = 'video/x-flv';
				break;
			case 'htm':
			case 'html':
			case 'shtm':
			case 'shtml':
				$tType = 'text/html';
				break;
			case 'php':
				$tType = 'application/x-httpd-php';
				break;
			case 'phps':
				$tType = 'application/x-httpd-php-source';
				break;
			case 'css':
				$tType = 'text/css';
				break;
			case 'js':
				$tType = 'application/x-javascript';
				break;
			case 'json':
				$tType = 'application/json';
				break;
			case 'c':
			case 'h':
				$tType = 'text/x-c';
				break;
			case 'py':
				$tType = 'application/x-python';
				break;
			case 'sh':
				$tType = 'text/x-shellscript';
				break;
			default:
				$tType = $uDefault;
			}

			return $tType;
		}

		/**
		 * @ignore
		 */
		public static function map($uPath, $uPattern = null, $uRecursive = true, $uBasenames = false) {
			$tArray = array('.' => array());
			$tDir = new \DirectoryIterator($uPath);

			foreach($tDir as $tFile) {
				$tFileName = $tFile->getFilename();

				if($tFileName[0] == '.') { // $tFile->isDot()
					continue;
				}

				if($tFile->isDir()) {
					if($uRecursive) {
						$tArray[$tFileName] = self::map($uPath . '/' . $tFileName, $uPattern, true, $uBasenames);
						continue;
					}

					$tArray[$tFileName] = null;
					continue;
				}

				if($tFile->isFile() && (is_null($uPattern) || fnmatch($uPattern, $tFileName))) {
					$tArray['.'][] = ($uBasenames ? pathinfo($tFileName, PATHINFO_FILENAME) : $tFileName);
				}
			}

			return $tArray;
		}

		/**
		 * @ignore
		 */
		public static function mapFlatten($uPath, $uPattern = null, $uRecursive = true, $uBasenames = false, &$uArray = null, $uPrefix = '') {
			if(is_null($uArray)) {
				$uArray = array();
			}

			$tDir = new \DirectoryIterator($uPath);

			foreach($tDir as $tFile) {
				$tFileName = $tFile->getFilename();

				if($tFileName[0] == '.') { // $tFile->isDot()
					continue;
				}

				if($tFile->isDir()) {
					if($uRecursive) {
						$tDirectory = $uPrefix . $tFileName . '/';
						// $uArray[] = $tDirectory;
						self::mapFlatten($uPath . '/' . $tFileName, $uPattern, true, $uBasenames, $uArray, $tDirectory);
					}

					continue;
				}

				if($tFile->isFile() && (is_null($uPattern) || fnmatch($uPattern, $tFileName))) {
					$uArray[] = $uPrefix . ($uBasenames ? pathinfo($tFileName, PATHINFO_FILENAME) : $tFileName);
				}
			}

			return $uArray;
		}

		/**
		 * @ignore
		 */
		public static function read($uPath, $uFlags = LOCK_SH) {
			if(!is_readable($uPath)) {
				return false;
			}

			$tHandle = fopen($uPath, 'r', false);
			if($tHandle === false) {
				return false;
			}

			$tLock = flock($tHandle, $uFlags);
			if($tLock === false) {
				fclose($tHandle);

				return false;
			}

			$tContent = stream_get_contents($tHandle);
			flock($tHandle, LOCK_UN);
			fclose($tHandle);

			return $tContent;
		}

		/**
		 * @ignore
		 */
		public static function write($uPath, $uContent, $uFlags = LOCK_EX) {
			$tHandle = fopen($uPath, 'w', false);
			if($tHandle === false) {
				return false;
			}

			if(flock($tHandle, $uFlags) === false) {
				fclose($tHandle);

				return false;
			}

			fwrite($tHandle, $uContent);
			fflush($tHandle);
			flock($tHandle, LOCK_UN);
			fclose($tHandle);

			return true;
		}

		/**
		 * @ignore
		 */
		public static function readSerialize($uPath, $uKeyphase = null) {
			$tContent = self::read($uPath);

			//! ambiguous return value
			if($tContent === false) {
				return false;
			}

			if(!is_null($uKeyphase) && strlen($uKeyphase) > 0) {
				$tContent = string::decrypt($tContent, $uKeyphase);
			}

			return unserialize($tContent);
		}

		/**
		 * @ignore
		 */
		public static function writeSerialize($uPath, $uContent, $uKeyphase = null) {
			$tContent = serialize($uContent);

			if(!is_null($uKeyphase) && strlen($uKeyphase) > 0) {
				$tContent = string::encrypt($tContent, $uKeyphase);
			}

			return self::write($uPath, $tContent);
		}

		/**
		 * @ignore
		 */
		public static function touch($uPath) {
			return touch($uPath);
		}

		/**
		 * @ignore
		 */
		public static function destroy($uPath) {
			if(file_exists($uPath)) {
				return unlink($uPath);
			}

			return false;
		}

		/**
		 * @ignore
		 */
		public static function sanitize($uFilename, $uRemoveAccent = false, $uRemoveSpaces = false) {
			static $sReplaceChars = array('\\' => '-', '/' => '-', ':' => '-', '?' => '-', '*' => '-', '"' => '-', '\'' => '-', '<' => '-', '>' => '-', '|' => '-', '.' => '-', '+' => '-');

			$tPathInfo = pathinfo($uFilename);
			$tFilename = strtr($tPathInfo['filename'], $sReplaceChars);

			if(isset($tPathInfo['extension'])) {
				$tFilename .= '.' . strtr($tPathInfo['extension'], $sReplaceChars);
			}

			$tFilename = string::removeInvisibles($tFilename);
			if($uRemoveAccent) {
				$tFilename = string::removeAccent($tFilename);
			}

			if($uRemoveSpaces) {
				$tFilename = strtr($tFilename, ' ', '_');
			}

			if(isset($tPathInfo['dirname']) && $tPathInfo['dirname'] != '.') {
				return rtrim(strtr($tPathInfo['dirname'], DIRECTORY_SEPARATOR, '/'), '/') . '/' . $tFilename;
			}

			return $tFilename;
		}
	}

	?>