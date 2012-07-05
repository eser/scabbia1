<?php

	if(!function_exists('fnmatch3')) {
		/**
		* @package Scabbia
		* @subpackage Core
		*/
		function fnmatch3($uPattern, $uString) {
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
			$tPath = pathinfo($uPattern, PATHINFO_DIRNAME);

			if(is_null($uArray)) {
				$uArray = array();
			}

			try {
				$tDir = new DirectoryIterator($tPath);

				foreach($tDir as $tFile) {
					if($tFile->isDot()) {
						continue;
					}

					$tFile2 = $tPath . '/' . $tFile->getFilename();

					if($tFile->isDir()) {
						$tFile2 .= '/';

						if($uRecursive) {
							$tBasename = pathinfo($uPattern, PATHINFO_BASENAME);
							$tPattern2 = $tFile2 . $tBasename;

							// $uArray = array_merge($tGlob, glob3($tPattern2, $uDirectories, true));
							glob3($tPattern2, $uDirectories, true, $uArray);
						}
					}

					if(!$uDirectories && !$tFile->isFile()) {
						continue;
					}

					if(fnmatch3($uPattern, $tFile2)) {
						$uArray[] = $tFile2;
					}
				}

				return $uArray;
			}
			catch(Exception $tException) { 
			}

			return false;
		}
	}

?>
