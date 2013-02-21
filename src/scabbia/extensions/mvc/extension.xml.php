<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>mvc</name>
		<version>1.1.0</version>
		<license>GPLv3</license>
		<phpversion>5.3.0</phpversion>
		<phpdependList />
		<fwversion>1.1</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
			<fwdepend>http</fwdepend>
			<fwdepend>router</fwdepend>
			<fwdepend>models</fwdepend>
			<fwdepend>views</fwdepend>
			<fwdepend>string</fwdepend>
			<fwdepend>io</fwdepend>
		</fwdependList>
	</info>
	<eventList>
		<event>
			<name>load</name>
			<callback>Scabbia\Extensions\Mvc\mvc::extensionLoad</callback>
		</event>
		<event>
			<name>httpUrl</name>
			<callback>Scabbia\Extensions\Mvc\mvc::httpUrl</callback>
		</event>
	</eventList>
</scabbia>