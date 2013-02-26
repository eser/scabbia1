<?php

	function autoload($uClassName) {
		$tBasePath = strtr(__DIR__ . '/src/', DIRECTORY_SEPARATOR, '/');
		$tClassName = ltrim($uClassName, '\\');
		$tFilename  = '';
		$tNamespace = '';

		if($tLastNsPos = strrpos($tClassName, '\\')) {
			$tNamespace = substr($tClassName, 0, $tLastNsPos);
			$tClassName = substr($tClassName, $tLastNsPos + 1);
			$tFilename = str_replace('\\', '/', $tNamespace) . '/';
		}

		$tFilename .= str_replace('_', '/', $tClassName) . '.php';
		require($tBasePath . $tFilename);
	}

	spl_autoload_register('autoload');

?>