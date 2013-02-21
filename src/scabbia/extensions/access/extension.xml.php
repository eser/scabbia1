<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>access</name>
		<version>1.1.0</version>
		<license>GPLv3</license>
		<phpversion>5.3.0</phpversion>
		<phpdependList />
		<fwversion>1.1</fwversion>
		<fwdependList />
	</info>
	<eventList>
		<event>
			<name>run</name>
			<callback>Scabbia\Extensions\Access\access::run</callback>
		</event>
	</eventList>
</scabbia>