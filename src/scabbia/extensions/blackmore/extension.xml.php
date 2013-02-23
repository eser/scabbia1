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
			<type>loadClass</type>
			<value>Scabbia\Extensions\Blackmore\blackmore</value>
		</event>
		<event>
			<name>blackmoreRegisterModules</name>
			<type>callback</type>
			<value>Scabbia\Extensions\Blackmore\blackmoreScabbia::blackmoreRegisterModules</value>
		</event>
		<event>
			<name>blackmoreRegisterModules</name>
			<type>callback</type>
			<value>Scabbia\Extensions\Blackmore\blackmoreZmodels::blackmoreRegisterModules</value>
		</event>
	</eventList>
</scabbia>