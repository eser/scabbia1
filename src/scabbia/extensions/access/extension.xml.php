<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>access</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.3.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList />
	</info>
	<includeList>
		<include>access.php</include>
	</includeList>
	<classList>
		<class>access</class>
	</classList>
	<eventList>
		<event>
			<name>run</name>
			<callback>Scabbia\Extensions\Access\access::run</callback>
		</event>
	</eventList>
</scabbia>