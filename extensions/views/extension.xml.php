<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<info>
		<name>views</name>
		<version>1.0.2</version>
		<license>GPLv3</license>
		<phpversion>5.2.0</phpversion>
		<phpdependList />
		<fwversion>1.0</fwversion>
		<fwdependList>
			<fwdepend>string</fwdepend>
			<fwdepend>http</fwdepend>
			<fwdepend>resources</fwdepend>
		</fwdependList>
	</info>
	<includeList>
		<include>views.php</include>
		<include>view.php</include>
		<include>viewEnginePhp.php</include>

		<!-- markdown -->
		<include>markdownExtra/markdownExtra.php</include>
		<include>markdownExtra/markdownParser.php</include>
		<include>markdownExtra/markdownExtraParser.php</include>
		<include>viewEngineMarkdown.php</include>

		<!-- razor -->
		<include>razor/RazorViewRenderer.php</include>
		<include>razor/RazorViewRendererException.php</include>
		<include>viewEngineRazor.php</include>

		<include>viewEnginePhptal.php</include>
		<include>viewEngineRaintpl.php</include>
		<include>viewEngineSmarty.php</include>
		<include>viewEngineTwig.php</include>
	</includeList>
	<classList>
		<class>views</class>
		<class>view</class>
		<class>viewEnginePhp</class>
		<class>viewEngineMarkdown</class>
		<class>viewEnginePhptal</class>
		<class>viewEngineRaintpl</class>
		<class>viewEngineRazor</class>
		<class>viewEngineSmarty</class>
		<class>viewEngineTwig</class>
	</classList>
	<eventList>
		<event>
			<name>load</name>
			<callback>views::extensionLoad</callback>
		</event>
		<event>
			<name>load</name>
			<callback>viewEngineMarkdown::extensionLoad</callback>
		</event>
		<event>
			<name>load</name>
			<callback>viewEnginePhptal::extensionLoad</callback>
		</event>
		<event>
			<name>load</name>
			<callback>viewEngineRaintpl::extensionLoad</callback>
		</event>
		<event>
			<name>load</name>
			<callback>viewEngineRazor::extensionLoad</callback>
		</event>
		<event>
			<name>load</name>
			<callback>viewEngineSmarty::extensionLoad</callback>
		</event>
		<event>
			<name>load</name>
			<callback>viewEngineTwig::extensionLoad</callback>
		</event>
	</eventList>
</scabbia>