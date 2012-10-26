<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>users</name>
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
		<include>users.php</include>
	</includeList>
	<classList>
		<class>users</class>
	</classList>
	<events>
		<loadList>
			<load>users::extension_load</load>
		</loadList>
	</events>
</scabbia>