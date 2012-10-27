<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>cache</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>io</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>cache.php</include>
	</includeList>
	<classList>
		<class>cache</class>
	</classList>
	<eventList>
		<event>
			<name>load</name>
			<callback>cache::extension_load</callback>
		</event>
	</eventList>
</scabbia>