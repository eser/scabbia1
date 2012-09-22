<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<options>
		<gzip value="1" />
<!--
		<scope mode="development">
			<siteroot value="/beta/" />
		</scope>
		<scope mode="production">
			<siteroot value="/" />
		</scope>
-->
	</options>

	<downloadList>
		<!-- <download filename="stopwatch2.php" url="http://localhost/blackmorep/res/stopwatch2.txt" /> -->
	</downloadList>

	<includeList>
		<include path="{core}includes/3rdparty/facebook-php-sdk-3.1.1/src/base_facebook.php" />
		<include path="{core}extensions/access.php" />
		<include path="{core}extensions/collections.php" />
		<include path="{core}extensions/unittest.php" />
		<include path="{core}extensions/media.php" />
		<include path="{core}extensions/captcha.php" />
		<include path="{core}extensions/fb.php" />
		<include path="{core}extensions/viewengine_razor.php" />
		<include path="{app}includes/*.php" />
		<include path="{app}extensions/*.php" />
		<include path="{app}writable/downloaded/*.php" />
		<include path="{app}controllers/*.php" />
		<include path="{app}models/*.php" />
	</includeList>

	<extensionList>
		<extension name="access" />
		<extension name="collections" />
		<extension name="unittest" />
		<extension name="media" />
		<extension name="captcha" />
		<extension name="fb" />
		<extension name="viewengine_razor" />
<!--
		<extension name="viewengine_php" />
		<extension name="viewengine_phptal" />
		<extension name="viewengine_smarty" />
		<extension name="viewengine_raintpl" />
		<extension name="viewengine_twig" />
-->
	</extensionList>

	<i8n>
<!--
		<routing
			languageUrlKey="0" />
-->

		<languageList>
			<language id="tr" locale="tr_TR.UTF-8" localewin="Turkish_Turkey.1254" internalEncoding="UTF-8">Turkish</language>
			<language id="en" locale="en_US.UTF-8" localewin="English_United States.1252" internalEncoding="UTF-8">English</language>
		</languageList>
	</i8n>

	<logger
		filename="{date|'d-m-Y'} {@category}.txt"
		line="[{date|'d-m-Y H:i:s'}] {strtoupper|@category} | {@ip} | {@message}"
		/>

	<cache
		keyphase=""
		storage="memcache://127.0.0.1:11211"
		/>
</scabbia>