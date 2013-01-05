<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>auth</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>session</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>auth.php</include>
	</includeList>
	<classList>
		<class>auth</class>
	</classList>
	<eventList>
		<event>
			<name>load</name>
			<callback>Scabbia\auth::extensionLoad</callback>
		</event>
	</eventList>
</scabbia>