<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>i8n</name>
		<version>1.1.0</version>
		<license>GPLv3</license>
		<phpversion>5.3.0</phpversion>
		<phpdependList>
			<phpdepend>mbstring</phpdepend>
		</phpdependList>
		<fwversion>1.1</fwversion>
		<fwdependList />
	</info>
	<eventList>
		<event>
			<name>httpUrl</name>
			<type>callback</type>
			<value>Scabbia\Extensions\I8n\i8n::httpUrl</value>
		</event>
	</eventList>
</scabbia>