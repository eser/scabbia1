<?php

	if(!function_exists('fnmatch')) { function fnmatch($uPattern, $uString) {
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
	} }

	if(!function_exists('glob2')) { function glob2($uPattern) {
		$tPath = pathinfo($uPattern, PATHINFO_DIRNAME);

		if(($tDir = opendir($tPath)) !== false) {
			$tGlob = array();
			while(($tFile = readdir($tDir)) !== false) {
				if($tFile == '.' || $tFile == '..') {
					continue;
				}
				$tFile2 = $tPath . '/' . $tFile;

				if(fnmatch($uPattern, $tFile2)) {
					$tGlob[] = $tFile2;
				}
			}
			closedir($tDir);

			return $tGlob;
		}

		return false;
	} }

?>