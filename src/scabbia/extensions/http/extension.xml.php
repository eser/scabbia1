<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>http</name>
		<version>1.1.0</version>
		<license>GPLv3</license>
		<phpversion>5.3.0</phpversion>
		<phpdependList />
		<fwversion>1.1</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
		</fwdependList>
	</info>
	<eventList>
		<event>
			<name>load</name>
			<type>callback</type>
			<value>Scabbia\Extensions\Http\http::extensionLoad</value>
		</event>
		<event>
			<name>load</name>
			<type>callback</type>
			<value>Scabbia\Extensions\Http\request::extensionLoad</value>
		</event>
		<event>
			<name>output</name>
			<type>callback</type>
			<value>Scabbia\Extensions\Http\http::output</value>
		</event>
	</eventList>
</scabbia>