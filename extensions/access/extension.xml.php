<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>access</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList />
	</info>
	<includeList>
		<include>access.php</include>
	</includeList>
	<classList>
		<class>access</class>
	</classList>
	<events>
		<loadList>
			<load>access::extension_load</load>
		</loadList>
	</events>
</scabbia>