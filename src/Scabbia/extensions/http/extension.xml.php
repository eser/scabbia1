<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>http</name>
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
		<include>http.php</include>
	</includeList>
	<classList>
		<class>http</class>
	</classList>
	<eventList>
		<event>
			<name>load</name>
			<callback>Scabbia\Extensions\Http\http::extensionLoad</callback>
		</event>
		<event>
			<name>output</name>
			<callback>Scabbia\Extensions\Http\http::output</callback>
		</event>
	</eventList>
</scabbia>