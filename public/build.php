<?php

// OWNER:	Eser 'Laroux' Ozvataf
// CONTACT:	eser@sent.com

// Last Comparision:
// 0.0014340877532959
// 0.0048351287841797

// Call 
	include('framework.php');

	Framework::build('index.php');
	Framework::purgeCompiledTemplates();

	echo 'done.';

?>
