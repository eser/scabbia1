<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>zmodels</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList />
	</info>
	<includeList>
		<include>zmodels.php</include>
		<include>zmodel.php</include>
	</includeList>
	<classList>
		<class>zmodels</class>
		<class>zmodel</class>
	</classList>
	<eventList>
		<event>
			<name>load</name>
			<callback>Scabbia\Extensions\ZModels\zmodels::extensionLoad</callback>
		</event>
	</eventList>
</scabbia>