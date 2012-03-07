<?php

	include('framework.php');
	
	$tHandle = fopen('output.csv', 'w');
	fputcsv($tHandle, array('uuid', 'mail', 'name'));

	// $tQuery = database::get()->queryFetch('SELECT uuid, LongName, EMail FROM users LIMIT 0, 15'); // 4000
	$tQuery = database::get('dbconn', 'getCsvOutput')->queryFetch(0, 15); // 4000
	foreach($tQuery as $tRow) { // 4000
		$tArray = array(
			'uuid' => $tRow['uuid'],
			'mail' => $tRow['EMail'],
			'mail' => $tRow['LongName']
		);
		
		fputcsv($tHandle, $tArray);
	}
	
	fclose($tHandle);
	
	echo 'done.';

?>
