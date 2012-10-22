<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<extension>
		<name>database</name>
		<version>1.0.2</version>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
			<fwdepend>cache</fwdepend>
		</fwdependList>
		<includeList>
			<include>database.php</include>
			<scope phpextension="pdo">
				<include>databaseprovider_pdo.php</include>
			</scope>
			<include>databaseprovider_mysql.php</include>
		</includeList>
		<events>
			<loadList>
				<load>database::extension_load</load>
			</loadList>
		</events>
	</extension>
</scabbia>