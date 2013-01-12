<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>router</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
			<fwdepend>http</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>router.php</include>
	</includeList>
	<classList>
		<class>router</class>
	</classList>
	<eventList>
		<event>
			<name>run</name>
			<callback>Scabbia\router::run</callback>
		</event>
	</eventList>
</scabbia>