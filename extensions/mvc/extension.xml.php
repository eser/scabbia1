<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<extension>
		<name>mvc</name>
		<version>1.0.2</version>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
			<fwdepend>http</fwdepend>
			<fwdepend>resources</fwdepend>
		</fwdependList>
		<includeList>
			<include>mvc.php</include>
			<include>viewengine_markdown.php</include>
			<include>viewengine_phptal.php</include>
			<include>viewengine_raintpl.php</include>
			<include>viewengine_razor.php</include>
			<include>viewengine_smarty.php</include>
			<include>viewengine_twig.php</include>
		</includeList>
	</extension>
</scabbia>	<events>
			<loadList>
				<load>mvc::extension_load</load>
				<load>viewengine_markdown::extension_load</load>
				<load>viewengine_phptal::extension_load</load>
				<load>viewengine_raintpl::extension_load</load>
				<load>viewengine_razor::extension_load</load>
				<load>viewengine_smarty::extension_load</load>
				<load>viewengine_twig::extension_load</load>
			</loadList>
		</events>
	</extension>
</scabbia>