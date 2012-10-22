<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<extension>
		<name>resources</name>
		<version>1.0.2</version>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>io</fwdepend>
			<fwdepend>cache</fwdepend>
			<fwdepend>http</fwdepend>
		</fwdependList>
		<includeList>
			<include>resources.php</include>
		</includeList>
		<events>
			<loadList>
				<load>resources::extension_load</load>
			</loadList>
		</events>
	</extension>
</scabbia>