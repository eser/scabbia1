<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<extension>
		<name>cache</name>
		<version>1.0.2</version>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>io</fwdepend>
		</fwdependList>
		<includeList>
			<include>cache.php</include>
		</includeList>
		<events>
			<loadList>
				<load>cache::extension_load</load>
			</loadList>
		</events>
	</extension>
</scabbia>