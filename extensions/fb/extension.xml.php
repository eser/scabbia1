<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<extension>
		<name>fb</name>
		<version>1.0.2</version>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>session</fwdepend>
		</fwdependList>
		<includeList>
			<include>fb.php</include>
		</includeList>
	</extension>
</	<events>
			<loadList>
				<load>fb::extension_load</load>
			</loadList>
		</events>
	</extension>
</scabbia>