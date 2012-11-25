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
			<name>http_url</name>
			<callback>i8n::http_url</callback>
		</event>
	</eventList>
</scabbia>