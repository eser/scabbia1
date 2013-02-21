<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>blackmore</name>
		<version>1.1.0</version>
		<license>GPLv3</license>
		<phpversion>5.3.0</phpversion>
		<phpdependList />
		<fwversion>1.1</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
			<fwdepend>resources</fwdepend>
			<fwdepend>validation</fwdepend>
			<fwdepend>http</fwdepend>
			<fwdepend>auth</fwdepend>
			<fwdepend>zmodels</fwdepend>
		</fwdependList>
	</info>
	<eventList>
		<event>
			<name>registerControllers</name>
			<callback>Scabbia\Extensions\Blackmore\blackmore</callback>
			<callbackType>loadClass</callbackType>
		</event>
		<event>
			<name>blackmoreRegisterModules</name>
			<callback>Scabbia\Extensions\Blackmore\blackmoreScabbia::blackmoreRegisterModules</callback>
		</event>
		<event>
			<name>blackmoreRegisterModules</name>
			<callback>Scabbia\Extensions\Blackmore\blackmoreZmodels::blackmoreRegisterModules</callback>
		</event>
	</eventList>
</scabbia>