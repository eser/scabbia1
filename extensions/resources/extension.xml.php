<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>resources</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>io</fwdepend>
			<fwdepend>cache</fwdepend>
			<fwdepend>http</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>resources.php</include>
	</includeList>
	<classList>
		<class>resources</class>
	</classList>
	<events>
		<loadList>
			<load>resources::extension_load</load>
		</loadList>
	</events>
</scabbia>