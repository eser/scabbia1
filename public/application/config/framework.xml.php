<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<scope binding="*:80">
		<options>
			<development value="1" />
			<debug value="0" />
			<gzip value="1" />
			<!-- <siteroot value="/scabbia/" /> -->
		</options>

		<downloadList>
			<!-- <download filename="stopwatch2.php" url="http://localhost/blackmorep/res/stopwatch2.txt" /> -->
		</downloadList>
		<includeList>
			<include path="{core}include/3rdparty/facebook-php-sdk-3.1.1/src/base_facebook.php" />
			<include path="{core}extensions/*.php" />
			<include path="{app}downloaded/*.php" />
			<include path="{app}controllers/*.php" />
			<include path="{app}models/*.php" />
		</includeList>

		<extensionList>
			<extension name="string" />
			<extension name="io" />
			<extension name="http" />
			<extension name="access" />
			<extension name="time" />
			<extension name="collections" />
			<extension name="contracts" />
			<extension name="validation" />
			<extension name="unittest" />
			<extension name="database" />
			<extension name="session" />
			<extension name="output" />
			<extension name="repository" />
			<extension name="i8n" />
			<extension name="mvc" />
			<extension name="logger" />
			<extension name="html" />
			<extension name="viewrenderer_razor" />
			<extension name="viewrenderer_markdown" />
<!--
			<extension name="viewrenderer_php" />
			<extension name="viewrenderer_phptal" />
			<extension name="viewrenderer_smarty" />
			<extension name="viewrenderer_raintpl" />
			<extension name="viewrenderer_twig" />
-->
			<extension name="stopwatch" />
			<extension name="fb" />
		</extensionList>

		<i8n>
			<routing
				languageUrlKey="0" />

		<languageList>
			<language id="tr">Turkish</language>
			<language id="en">English</language>
		</languageList>
		</i8n>

		<logger
			filename="{date|'d-m-Y'} {@category}.txt"
			line="[{date|'d-m-Y H:i:s'}] {strtoupper|@category} | {@ip} | {@message}"
			/>
	</scope>
</scabbia>