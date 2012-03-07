<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<scope binding="*:80">
		<includeList>
			<include path="{core}extensions/*.php" />
			<include path="{app}controllers/*.php" />
			<include path="{app}models/*.php" />
		</includeList>

		<extensionList>
			<extension name="string" />
			<extension name="io" />
			<extension name="http" />
			<extension name="time" />
			<extension name="collections" />
			<extension name="output" />
			<extension name="contracts" />
			<extension name="viewrenderer_razor" />
			<extension name="viewrenderer_markdown" />
			<extension name="database" />
			<extension name="stopwatch" />
			<extension name="mvc" />
			<extension name="html" />
<!--
			<extension name="viewrenderer_php" />
			<extension name="viewrenderer_phptal" />
			<extension name="viewrenderer_smarty" />
			<extension name="viewrenderer_raintpl" />
			<extension name="viewrenderer_twig" />
-->
		</extensionList>

		<languageList>
			<language id="tr">Turkish</language>
			<language id="en">English</language>
		</languageList>
	</scope>
</scabbia>