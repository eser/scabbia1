<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<extension>
		<name>string</name>
		<version>1.0.2</version>
		<phpversion>5.2.0</phpversion>
		<phpdependList>
			<phpdepend>mbstring</phpdepend>
		</phpdependList>
		<fwversion>1.0</fwversion>
		<fwdependList />
		<includeList>
			<include>string.php</include>
		</includeList>
		<events>
			<loadList>
				<load>string::extension_load</load>
			</loadList>
		</events>
	</extension>
</scabbia>