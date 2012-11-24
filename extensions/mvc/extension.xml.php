<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>mvc</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
			<fwdepend>http</fwdepend>
			<fwdepend>resources</fwdepend>
			<fwdepend>models</fwdepend>
			<fwdepend>views</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>mvc.php</include>
	</includeList>
	<classList>
		<class>mvc</class>
		<class>controller</class>
	</classList>
	<eventList>
		<event>
			<name>load</name>
			<callback>mvc::extension_load</callback>
		</event>
		<event>
			<name>http_route</name>
			<callback>mvc::http_route</callback>
		</event>
	</eventList>
</scabbia>