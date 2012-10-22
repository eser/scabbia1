<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<moduleList />

	<includeList>
		<include>{core}extensions/string/string.php</include>
		<include>{core}extensions/io/io.php</include>
		<include>{core}extensions/http/http.php</include>
		<include>{core}extensions/arrays/arrays.php</include>
		<include>{core}extensions/time/time.php</include>
		<include>{core}extensions/contracts/contracts.php</include>
		<include>{core}extensions/validation/validation.php</include>
		<scope mode="development">
			<include>{core}extensions/profiler/profiler.php</include>
		</scope>
		<include>{core}extensions/cache/cache.php</include>
		<scope mode="development">
			<include>{core}extensions/database/database.php</include>
			<scope phpextension="pdo">
				<include>{core}extensions/database/databaseprovider_pdo.php</include>
			</scope>
			<include>{core}extensions/database/databaseprovider_mysql.php</include>
		</scope>
		<include>{core}extensions/session/session.php</include>
		<include>{core}extensions/logger/logger.php</include>
		<include>{core}extensions/html/html.php</include>
		<include>{core}extensions/i8n/i8n.php</include>
		<include>{core}extensions/resources/resources.php</include>
		<include>{core}extensions/mvc/mvc.php</include>
		<scope mode="development">
			<include>{core}extensions/mvc/viewengine_markdown.php</include>
			<include>{core}extensions/docs/docs.php</include>
			<include>{core}extensions/blackmore/blackmore.php</include>
			<include>{core}extensions/blackmore/blackmore_categories.php</include>
		</scope>
		<!-- <include>{core}extensions/oauth/oauth.php</include> -->
		<!-- <include>{core}controllers/*.php</include> -->
		<!-- <include>{core}models/*.php</include> -->
	</includeList>

	<extensionList>
		<extension>string</extension>
		<extension>io</extension>
		<extension>http</extension>
		<extension>arrays</extension>
		<extension>time</extension>
		<extension>contracts</extension>
		<extension>validation</extension>
		<scope mode="development">
			<extension>profiler</extension>
		</scope>
		<extension>cache</extension>
		<scope mode="development">
			<extension>database</extension>
			<scope phpextension="pdo">
				<extension>databaseprovider_pdo</extension>
			</scope>
			<extension>databaseprovider_mysql</extension>
		</scope>
		<extension>session</extension>
		<extension>logger</extension>
		<extension>html</extension>
		<extension>i8n</extension>
		<extension>resources</extension>
		<extension>mvc</extension>
		<scope mode="development">
			<extension>viewengine_markdown</extension>
			<extension>docs</extension>
			<extension>blackmore</extension>
			<extension>blackmore_categories</extension>
		</scope>
	</extensionList>
</scabbia>