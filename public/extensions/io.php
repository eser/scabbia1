<?php

if(extensions::isSelected('io')) {
	/**
	* IO Extension
	*
	* @package Scabbia
	* @subpackage UtilityExtensions
	*/
	class io {
		public static function extension_info() {
			return array(
				'name' => 'io',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('string')
			);
		}

		public static function getMimeType($uExtension, $uDefault = 'application/octet-stream') {
			switch(string::toLower($uExtension)) {
			case 'pdf':
				$tType = 'application/pdf'; break;
			case 'exe':
				$tType = 'application/octet-stream'; break;
			case 'dll':
				$tType = 'application/x-msdownload'; break;
			case 'zip':
				$tType = 'application/zip'; break;
			case 'rar':
				$tType = 'application/x-rar-compressed'; break;
			case 'gz':
				$tType = 'application/x-gzip'; break;
			case 'tar':
				$tType = 'application/x-tar'; break;
			case 'deb':
				$tType = 'application/x-deb'; break;
			case 'dmg':
				$tType = 'application/x-apple-diskimage'; break;
			case 'csv':
				$tType = 'text/csv'; break;
			case 'txt':
			case 'text':
			case 'log':
			case 'ini':
				$tType = 'text/plain'; break;
			case 'rtf':
				$tType = 'text/rtf'; break;
			case 'odt':
				$tType = 'application/vnd.oasis.opendocument.text'; break;
			case 'eml':
				$tType = 'message/rfc822'; break;
			case 'xml':
			case 'xsl':
				$tType = 'text/xml'; break;
			case 'doc':
			case 'word':
				$tType = 'application/msword'; break;
			case 'docx':
				$tType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'; break;
			case 'xls':
				$tType = 'application/vnd.ms-excel'; break;
			case 'xl':
				$tType = 'application/excel'; break;
			case 'xlsx':
				$tType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; break;
			case 'ppt':
				$tType = 'application/vnd.ms-powerpoint'; break;
			case 'bmp':
				$tType = 'image/x-ms-bmp'; break;
			case 'gif':
				$tType = 'image/gif'; break;
			case 'png':
				$tType = 'image/png'; break;
			case 'jpeg':
			case 'jpe':
			case 'jpg':
				$tType = 'image/jpeg'; break;
			case 'webp':
				$tType = 'image/webp'; break;
			case 'tif':
			case 'tiff':
				$tType = 'image/tiff'; break;
			case 'psd':
				$tType = 'image/vnd.adobe.photoshop'; break;
			case 'mid':
			case 'midi':
				$tType = 'audio/midi'; break;
			case 'mpga':
			case 'mp2':
			case 'mp3':
				$tType = 'audio/mpeg'; break;
			case 'wav':
				$tType = 'audio/x-wav'; break;
			case 'aac':
				$tType = 'audio/aac'; break;
			case 'ogg':
				$tType = 'audio/ogg'; break;
			case 'wma':
				$tType = 'audio/x-ms-wma'; break;
			case 'mpeg':
			case 'mpg':
			case 'mpe':
				$tType = 'video/mpeg'; break;
			case 'mp4':
				$tType = 'application/mp4'; break;
			case 'qt':
			case 'mov':
				$tType = 'video/quicktime'; break;
			case 'avi':
				$tType = 'video/x-msvideo'; break;
			case 'wmv':
				$tType = 'video/x-ms-wmv'; break;
			case 'swf':
				$tType = 'application/x-shockwave-flash'; break;
			case 'flv':
				$tType = 'video/x-flv'; break;
			case 'htm':
			case 'html':
			case 'shtm':
			case 'shtml':
				$tType = 'text/html'; break;
			case 'php':
				$tType = 'application/x-httpd-php'; break;
			case 'phps':
				$tType = 'application/x-httpd-php-source'; break;
			case 'css':
				$tType = 'text/css'; break;
			case 'js':
				$tType = 'application/x-javascript'; break;
			case 'json':
				$tType = 'application/json'; break;
			case 'c':
			case 'h':
				$tType = 'text/x-c'; break;
			case 'py':
				$tType = 'application/x-python'; break;
			case 'sh':
				$tType = 'text/x-shellscript'; break;
			default:
				$tType = $uDefault;
			}

			return $tType;
		}

		public static function map($uPath, $uPattern = null, $uRecursive = true) {
			$tArray = array();
			$tDir = new DirectoryIterator($uPath);

			foreach($tDir as $tFile) {
				if($tFile->isDot()) {
					continue;
				}

				$tFile2 = $tFile->getFilename();

				if($tFile->isDir()) {
					if($uRecursive) {
						$tArray[$tFile2] = self::map($uPath . '/' . $tFile2, $uPattern, true);
					}
					else {
						$tArray[$tFile2] = null;
					}
					continue;
				}

				if($tFile->isFile() && (is_null($uPattern) || fnmatch3($uPattern, $tFile2))) {
					$tArray[] = $tFile2;
				}
			}

			return $tArray;
		}

		public static function read($uPath) {
			return file_get_contents($uPath);
		}

		public static function write($uPath, $uContent) {
			return file_put_contents($uPath, $uContent, LOCK_EX);
		}

		public static function readSerialize($uPath, $uEncryptKey = null) {
			$tContent = self::read($uPath);

			if(!is_null($uEncryptKey) && strlen($uEncryptKey) > 0) {
				$tContent = string::decrypt($tContent, $uEncryptKey);
			}

			return unserialize($tContent);
		}

		public static function writeSerialize($uPath, $uContent, $uEncryptKey = null) {
			$tContent = serialize($uContent);

			if(!is_null($uEncryptKey) && strlen($uEncryptKey) > 0) {
				$tContent = string::encrypt($tContent, $uEncryptKey);
			}

			return self::write($uPath, $tContent);
		}

		public static function touch($uPath) {
			return touch($uPath);
		}
		
		public static function sanitize($uFilename) {
			static $aReplaceChars = array('_' => '-', '\\' => '-', '/' => '-', ':' => '-', '?' => '-', '*' => '-', '"' => '-', '\'' => '-', '<' => '-', '>' => '-', '|' => '-', '.' => '-');

			return strtr($uFilename, $aReplaceChars);
		}
	}
}

?>