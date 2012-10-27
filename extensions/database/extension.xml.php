<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>database</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
			<fwdepend>cache</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>database.php</include>
		<scope phpextension="pdo">
			<include>databaseprovider_pdo.php</include>
		</scope>
		<include>databaseprovider_mysql.php</include>
	</includeList>
	<classList>
		<class>database</class>
		<class>databaseConnection</class>
		<class>databaseDataset</class>
		<class>databaseQuery</class>
		<class>databaseQueryResult</class>
		<scope phpextension="pdo">
			<class>databaseprovider_pdo</class>
		</scope>
		<class>databaseprovider_mysql</class>
	</classList>
</scabbia>