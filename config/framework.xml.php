<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<moduleList />

	<includeList>
		<include>{core}extensions/string.php</include>
		<include>{core}extensions/io.php</include>
		<include>{core}extensions/http.php</include>
		<include>{core}extensions/arrays.php</include>
		<include>{core}extensions/time.php</include>
		<include>{core}extensions/contracts.php</include>
		<include>{core}extensions/validation.php</include>
		<scope mode="development">
			<include>{core}extensions/profiler.php</include>
		</scope>
		<include>{core}extensions/cache.php</include>
		<scope mode="development">
			<include>{core}extensions/database.php</include>
			<scope phpextension="pdo">
				<include>{core}extensions/databaseprovider_pdo.php</include>
			</scope>
			<include>{core}extensions/databaseprovider_mysql.php</include>
		</scope>
		<include>{core}extensions/session.php</include>
		<include>{core}extensions/logger.php</include>
		<include>{core}extensions/html.php</include>
		<include>{core}extensions/i8n.php</include>
		<include>{core}extensions/resources.php</include>
		<include>{core}extensions/mvc.php</include>
		<scope mode="development">
			<include>{core}extensions/viewengine_markdown.php</include>
			<include>{core}extensions/docs.php</include>
			<include>{core}extensions/blackmore.php</include>
		</scope>
		<!-- <include>{core}extensions/oauth.php</include> -->
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
		</scope>
	</extensionList>
</scabbia>