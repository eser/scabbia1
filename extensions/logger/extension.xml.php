<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>logger</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>logger.php</include>
	</includeList>
	<classList>
		<class>logger</class>
	</classList>
	<eventList>
		<event>
			<name>load</name>
			<callback>logger::extensionLoad</callback>
		</event>
	</eventList>
</scabbia>