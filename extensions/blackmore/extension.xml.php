<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>blackmore</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
			<fwdepend>resources</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>blackmore.php</include>
		<include>blackmore_categories.php</include>
	</includeList>
	<classList>
		<class>blackmore</class>
		<class>blackmore_categories</class>
	</classList>
	<events>
		<loadList>
			<load>blackmore::extension_load</load>
			<load>blackmore_categories::extension_load</load>
		</loadList>
	</events>
</scabbia>