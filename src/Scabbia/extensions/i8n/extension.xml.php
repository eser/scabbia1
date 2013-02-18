<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>i8n</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList>
			<phpdepend>mbstring</phpdepend>
		</phpdependList>
		<fwversion>1.0</fwversion>
		<fwdependList />
	</info>
	<includeList>
		<include>i8n.php</include>
	</includeList>
	<classList>
		<class>i8n</class>
	</classList>
	<eventList>
		<event>
			<name>httpUrl</name>
			<callback>Scabbia\Extensions\I8n\i8n::httpUrl</callback>
		</event>
	</eventList>
</scabbia>