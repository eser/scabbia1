<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>fb</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>session</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>facebook-php-sdk-3.1.1/src/base_facebook.php</include>
		<include>fb.php</include>
	</includeList>
	<classList>
		<class>fb</class>
		<class>FacebookQueryObject</class>
		<class>Facebook</class>
		<class>BaseFacebook</class>
	</classList>
</scabbia>