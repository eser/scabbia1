<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<extension>
		<name>media</name>
		<version>1.0.2</version>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList />
		<includeList>
			<include>media.php</include>
		</includeList>
		<events>
			<loadList>
				<load>media::extension_load</load>
			</loadList>
		</events>
	</extension>
</scabbia>