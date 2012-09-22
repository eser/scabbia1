<?php

// OWNER:	Eser 'Laroux' Ozvataf
// CONTACT:	eser@sent.com

// This file loads the non-minified version of framework,
// in order to create a new compilation. Some of routines
// are extracted in compiled version to avoid unnecessary
// run-time controls such as version and dependency checks.

// Checks existing PHP version
	if(!function_exists('version_compare') || version_compare(PHP_VERSION, '5.1.9', '<')) {
		exit('Scabbia framework requires PHP 5.2 or later - Current: ' . PHP_VERSION);
	}

// Prevents code termination in case of connection breakdown.
	ignore_user_abort();

// Use the Universal Coordinated Time and most common English standards
	date_default_timezone_set('UTC');

// Constant definitions
	define('PHP_OS_WINDOWS', (DIRECTORY_SEPARATOR == '\\'));
	define('PHP_SAPI_CLI', (PHP_SAPI == 'cli'));
	define('QPATH_BASE', strtr(pathinfo(__FILE__, PATHINFO_DIRNAME), DIRECTORY_SEPARATOR, '/') . '/');
	define('QPATH_CORE', QPATH_BASE . 'core/');
	define('QTIME_INIT', microtime(true));
	define('QEXT_PHP', '.' . pathinfo(__FILE__, PATHINFO_EXTENSION));

	define('SCABBIA_VERSION', '1.0.' . ceil(QTIME_INIT / 86400));
	define('INCLUDED', 'Scabbia ' . SCABBIA_VERSION);
	define('COMPILED', false);

	define('OUTPUT_NOHANDLER', (ini_get('output_handler') == ''));
	define('OUTPUT_GZIP', (OUTPUT_NOHANDLER && !ini_get('zlib.output_compression')));
	define('OUTPUT_MULTIBYTE', OUTPUT_NOHANDLER);

// Include framework dependencies and load them
	require(QPATH_CORE . 'includes/patches.main' . QEXT_PHP);
	require(QPATH_CORE . 'includes/config.main' . QEXT_PHP);
	require(QPATH_CORE . 'includes/events.main' . QEXT_PHP);
	require(QPATH_CORE . 'includes/framework.main' . QEXT_PHP);
	require(QPATH_CORE . 'includes/extensions.main' . QEXT_PHP);

	config::init();
	// events::init();
	framework::init();
	// extensions::init();

	config::load();
	// events::load();
	framework::load();
	extensions::load();

	// config::run();
	// events::run();
	framework::run();
	extensions::run();

?>