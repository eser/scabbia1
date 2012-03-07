<?php

	include('framework.php');

	$tHandle = fopen('output.csv', 'w');
	
	$tDatabase = database::get();
	$tPointer = $tDatabase->fetchStart('SELECT uuid, LongName, EMail FROM users LIMIT 0, 5');
	while($tRow = $tDatabase->fetchNext($tPointer)) {
		fputcsv($tHandle, $tRow);
	}
	$tDatabase->fetchStop($tPointer);
	
	fclose($tHandle);

?>