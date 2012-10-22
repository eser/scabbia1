<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<extension>
		<name>logger</name>
		<version>1.0.2</version>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
		</fwdependList>
		<includeList>
			<include>logger.php</include>
		</includeList>
	</extension>
</	<events>
			<loadList>
				<load>logger::extension_load</load>
			</loadList>
		</events>
	</extension>
</scabbia>