<?php

// OWNER:	   Eser 'Laroux' Ozvataf
// CONTACT:	   eser@sent.com
// REPOSITORY: https://github.com/larukedi/Scabbia-Framework/

// Front to the Scabbia Framework applications. This file
// loads and executes the non-minified version of framework.
//
// In order to build a compiled/minified version of
// framework, visit {webroot}/scabbia while development mode
// is enabled from configuration. Some of routines may not
// contained in compiled version to avoid unnecessary
// run-time controls such as version and dependency checks.

// Checks existing PHP version
	if(!function_exists('version_compare') || version_compare(PHP_VERSION, '5.2.0', '<')) {
		trigger_error('Scabbia framework requires PHP 5.2.0 or later - Current: ' . PHP_VERSION, E_USER_ERROR);
		exit;
	}

// Prevents code termination in case of connection breakdown.
	ignore_user_abort();

// Constant definitions
	define('PHP_SAPI_CLI', (PHP_SAPI == 'cli'));
	define('PHP_SAFEMODE', (version_compare(PHP_VERSION, '5.3.0', '<') && ini_get('safe_mode')));
	if(!defined('QPATH_BASE')) {
		define('QPATH_BASE', strtr(pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_DIRNAME), DIRECTORY_SEPARATOR, '/') . '/');
	}
	define('QPATH_CORE', strtr(pathinfo(__FILE__, PATHINFO_DIRNAME), DIRECTORY_SEPARATOR, '/') . '/');
	define('QTIME_INIT', microtime(true));

	define('SCABBIA_VERSION', '1.0');
	define('COMPILED', false);

	define('OUTPUT_NOHANDLER', (ini_get('output_handler') == ''));
	define('OUTPUT_GZIP', (OUTPUT_NOHANDLER && !ini_get('zlib.output_compression')));

// Set error reporting occasions
	error_reporting(defined('E_STRICT') ? E_ALL | E_STRICT : E_ALL);
	// ini_set('display_errors', '1');
	// ini_set('log_errors', '0');
	// ini_set('error_log', QPATH_BASE . 'error.log');

// Include framework dependencies and load them
	require(QPATH_CORE . 'patches.php');
	require(QPATH_CORE . 'framework.php');
	require(QPATH_CORE . 'config.php');
	require(QPATH_CORE . 'events.php');
	require(QPATH_CORE . 'extensions.php');

	?>
