<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<extension>
		<name>session</name>
		<version>1.0.2</version>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>cache</fwdepend>
		</fwdependList>
		<includeList>
			<include>session.php</include>
		</includeList>
		<events>
			<loadList>
				<load>session::extension_load</load>
			</loadList>
		</events>
	</extension>
</scabbia>