<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<extension>
		<name>captcha</name>
		<version>1.0.2</version>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
			<fwdepend>session</fwdepend>
		</fwdependList>
		<includeList>
			<include>captcha.php</include>
		</includeList>
		<events>
			<loadList>
				<load>captcha::extension_load</load>
			</loadList>
		</events>
	</extension>
</scabbia>