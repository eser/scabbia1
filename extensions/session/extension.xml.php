<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>session</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>cache</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>session.php</include>
	</includeList>
	<classList>
		<class>session</class>
	</classList>
	<eventList>
		<event>
			<name>output</name>
			<callback>session::save</callback>
		</event>
	</eventList>
</scabbia>