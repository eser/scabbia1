<?php

	namespace Scabbia;

	/**
	 * Resources Extension
	 *
	 * @package Scabbia
	 * @subpackage resources
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends mime, io, cache, http
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 *
	 * @todo integrate with cache extension
	 */
	class resources {
		/**
		 * @ignore
		 */
		public static $packs = null;
		/**
		 * @ignore
		 */
		public static $directories = null;

		/**
		 * @ignore
		 */
		public static function httpRoute(&$uParms) {
			if(is_null(self::$packs)) {
				self::$packs = config::get('/resources/packList', array());

				foreach(config::get('/resources/fileList', array()) as $tFile) {
					self::$packs[] = array(
						'partList' => array(array('type' => $tFile['type'], 'name' => $tFile['name'])),
						'name' => $tFile['name'],
						'type' => $tFile['type'],
						'cacheTtl' => isset($tFile['cacheTtl']) ? $tFile['cacheTtl'] : 0
					);
				}

				self::$directories = config::get('/resources/directoryList', array());
			}

			if(isset($uParms['get']['_segments']) && count($uParms['get']['_segments']) > 0) {
				$tPath = implode('/', $uParms['get']['_segments']);

				foreach(self::$directories as $tDirectory) {
					$tDirectoryName = rtrim($tDirectory['name'], '/');
					$tLen = strlen($tDirectoryName);

					if(substr($tPath, 0, $tLen) == $tDirectoryName) {
						if(self::getDirectory($tDirectory, substr($tPath, $tLen)) === true) {
							// to interrupt event-chain execution
							return false;
						}
					}
				}

				$tSubParts = array();
				foreach($uParms['get'] as $tKey => $tSubPart) {
					if($tKey[0] == '_') {
						continue;
					}

					$tSubParts[] = $tKey;
				}

				if(self::getPack($tPath, $tSubParts) === true) {
					// to interrupt event-chain execution
					return false;
				}
			}
		}

		/**
		 * @ignore
		 */
		public static function getPack($uName, $uClasses = array()) {
			foreach(self::$packs as $tPack) {
				if($tPack['name'] != $uName) {
					continue;
				}

				$tSelectedPack = $tPack;
				break;
			}

			if(!isset($tSelectedPack)) {
				return false;
			}

			$tType = $tSelectedPack['type'];
			$tCacheTtl = isset($tSelectedPack['cacheTtl']) ? $tSelectedPack['cacheTtl'] : 0;
			$tFilename = $uName;
			foreach($uClasses as $tClassName) {
				$tFilename .= '_' . $tClassName;
			}
			$tFilename .= '.' . $tType;

			$tCompileAge = isset($tSelectedPack['compiledAge']) ? $tSelectedPack['compiledAge'] : 120;
			if(extensions::isLoaded('mime')) {
				$tMimetype = mime::getType($tType);
			}
			else {
				$tMimetype = 'application/octet-stream';
			}
			header('Content-Type: ' . $tMimetype, true);

			$tOutputFile = cache::filePath('resources/', $tFilename, $tCompileAge);
			if(framework::$development >= 1 || !$tOutputFile[0]) {
				$tContent = '';
				foreach($tSelectedPack['partList'] as $tPart) {
					$tType = isset($tPart['type']) ? $tPart['type'] : 'file';
					$tClass = isset($tPart['class']) ? $tPart['class'] : null;

					if(!is_null($tClass) && !in_array($tClass, $uClasses, true)) {
						continue;
					}

					if($tType == 'function') {
						$tContent .= call_user_func($tPart['name']);
					}
					else {
						switch($tMimetype) {
						case 'application/x-httpd-php':
						case 'application/x-httpd-php-source':
							$tContent .= framework::printFile(framework::translatePath($tPart['path']));
							break;
						case 'application/x-javascript':
							$tContent .= '/* JS: ' . $tPart['path'] . ' */' . PHP_EOL;
							$tContent .= io::read(framework::translatePath($tPart['path']));
							$tContent .= PHP_EOL;
							break;
						case 'text/css':
							$tContent .= '/* CSS: ' . $tPart['path'] . ' */' . PHP_EOL;
							$tContent .= io::read(framework::translatePath($tPart['path']));
							$tContent .= PHP_EOL;
							break;
						default:
							$tContent .= io::read(framework::translatePath($tPart['path']));
							break;
						}
					}
				}

				http::sendHeaderCache($tCacheTtl);

				switch($tMimetype) {
				case 'application/x-javascript':
					// $tContent = JSMin::minify($tContent);
					if(!is_null($tOutputFile[1])) {
						io::write($tOutputFile[1], $tContent);
					}
					echo $tContent;
					break;
				case 'text/css':
					// $tContent = CssMin::minify($tContent);
					if(!is_null($tOutputFile[1])) {
						io::write($tOutputFile[1], $tContent);
					}
					echo $tContent;
					break;
				default:
					if(!is_null($tOutputFile[1])) {
						io::write($tOutputFile[1], $tContent);
					}
					echo $tContent;
					break;
				}
			}
			else {
				readfile($tOutputFile[1]);
			}

			return true;
		}


		/**
		 * @ignore
		 */
		public static function getDirectory($uSelectedDirectory, $uSubPath) {
			$tPath = rtrim(framework::translatePath($uSelectedDirectory['path']), '/');

			foreach(explode('/', ltrim($uSubPath, '/')) as $tSubDirectory) {
				if(strlen($tSubDirectory) == 0 || $tSubDirectory[0] == '.') {
					break;
				}

				$tPath .= '/' . $tSubDirectory;
			}

			if(!file_exists($tPath)) {
				throw new \Exception('resource not found.');
			}

			if(isset($uSelectedDirectory['autoViewer'])) {
				if(is_dir($tPath)) {
					$tPath = rtrim($tPath, '/') . '/' . $uSelectedDirectory['autoViewer']['defaultPage'];
				}

				if(isset($uSelectedDirectory['autoViewer']['header'])) {
					views::viewFile($uSelectedDirectory['autoViewer']['header']);
				}

				views::viewFile($tPath);

				if(isset($uSelectedDirectory['autoViewer']['footer'])) {
					views::viewFile($uSelectedDirectory['autoViewer']['footer']);
				}

				return true;
			}

			if(is_dir($tPath)) {
				return false;
			}

			if(extensions::isLoaded('mime')) {
				header('Content-Type: ' . io::getMimeType(pathinfo($tPath, PATHINFO_EXTENSION)), true);
			}
			else {
				header('Content-Type: application/octet-stream', true);
			}
			header('Content-Transfer-Encoding: binary', true);
			// header('ETag: "' . md5_file($tPath) . '"', true);

			readfile($tPath);

			return true;
		}
	}

	?>