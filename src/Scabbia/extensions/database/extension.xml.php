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
			<fwdepend>datasources</fwdepend>
			<fwdepend>cache</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>database.php</include>
		<include>databaseConnection.php</include>
		<include>databaseDataset.php</include>
		<include>databaseQuery.php</include>
		<include>databaseQueryResult.php</include>
		<scope phpextension="pdo">
			<include>databaseProviderPdo.php</include>
		</scope>
		<include>databaseProviderMysql.php</include>
		<include>datasets.php</include>
	</includeList>
	<classList>
		<class>database</class>
		<class>databaseConnection</class>
		<class>databaseDataset</class>
		<class>databaseQuery</class>
		<class>databaseQueryResult</class>
		<scope phpextension="pdo">
			<class>databaseProviderPdo</class>
		</scope>
		<class>databaseProviderMysql</class>
		<class>datasets</class>
	</classList>
	<eventList>
		<event>
			<name>load</name>
			<callback>Scabbia\database::extensionLoad</callback>
		</event>
	</eventList>
</scabbia>