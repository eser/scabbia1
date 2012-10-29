<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>blackmorecms</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
			<fwdepend>resources</fwdepend>
			<fwdepend>auth</fwdepend>
			<fwdepend>validation</fwdepend>
			<fwdepend>http</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>blackmorecms_categories.php</include>
		<include>blackmorecms_categories_model.php</include>
	</includeList>
	<classList>
		<class>blackmorecms_categories</class>
		<class>blackmoreCmsCategoriesModel</class>
	</classList>
	<eventList>
		<event>
			<name>blackmore_registerModules</name>
			<callback>blackmorecms_categories::blackmore_registerModules</callback>
		</event>
	</eventList>
</scabbia>