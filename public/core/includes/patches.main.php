<?php

	if(!function_exists('fnmatch')) {
		/**
		* @package Scabbia
		* @subpackage Core
		*/
		function fnmatch($uPattern, $uString) {
			for($tBrackets = 0, $tPregPattern = '', $tCount = 0, $tLen = strlen($uPattern); $tCount < $tLen; $tCount++) {
				$tChar = $uPattern[$tCount];

				if(strpbrk($tChar, '\\/-+^$=!.|(){}<>')) {
					$tPregPattern .= '\\' . $tChar;
				}
				else if(strpbrk($tChar, '?*')) {
					$tPregPattern .= '.' . $tChar;
				}
				else {
					$tPregPattern .= $tChar;
					if($tChar == '[') {
						$tBrackets++;
					}
					else if($tChar == ']') {
						if($tBrackets == 0) {
							return false;
						}

						$tBrackets--;
					}
				}
			}

			if($tBrackets != 0) {
				return false;
			}

			return preg_match('/' . $tPregPattern . '/i', $uString);
		}
	}

	if(!function_exists('glob3')) {
		/**
		* @package Scabbia
		* @subpackage Core
		*/
		function &glob3($uPattern, $uDirectories = true, $uRecursive = false, &$uArray = null) {
			$tPath = strtr(pathinfo($uPattern, PATHINFO_DIRNAME), DIRECTORY_SEPARATOR, '/');

			if(is_null($uArray)) {
				$uArray = array();
			}

			try {
				$tDir = new DirectoryIterator($tPath);

				foreach($tDir as $tFile) {
					$tFileName = $tFile->getFilename();

					if($tFileName[0] == '.') { // $tFile->isDot()
						continue;
					}

					if($tFile->isDir()) {
						$tDirectory = $tPath . '/' . $tFileName . '/';

						if($uDirectories) {
							$uArray[] = $tDirectory;
						}

						if($uRecursive) {
							glob3(
								$tDirectory . pathinfo($uPattern, PATHINFO_BASENAME),
								$uDirectories,
								true,
								$uArray
							);
						}

						continue;
					}

					if($tFile->isFile()) {
						$tFile2 = $tPath . '/' . $tFileName;
						if(fnmatch($uPattern, $tFile2)) {
							$uArray[] = $tFile2;
						}

						continue;
					}
				}

				return $uArray;
			}
			catch(Exception $tException) {
			}

			$uArray = false;
			return $uArray;
		}
	}

?>