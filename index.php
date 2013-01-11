<?php

	require('framework.php');

	use Scabbia\framework;

	// framework::$endpoints[] = 'http://localhost/survey';
	framework::$development = 1;
	framework::$readonly = true;
	framework::load();

?>