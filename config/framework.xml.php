<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<moduleList />

	<includeList>
		<!-- <include>{core}controllers/*.php</include> -->
		<!-- <include>{core}models/*.php</include> -->
	</includeList>

	<extensionList>
		<extension>{core}extensions/string/</extension>
		<extension>{core}extensions/io/</extension>
		<extension>{core}extensions/http/</extension>
		<extension>{core}extensions/arrays/</extension>
		<extension>{core}extensions/time/</extension>
		<extension>{core}extensions/contracts/</extension>
		<extension>{core}extensions/validation/</extension>
		<scope mode="development">
			<extension>{core}extensions/profiler/</extension>
		</scope>
		<extension>{core}extensions/cache/</extension>
		<scope mode="development">
			<extension>{core}extensions/database/</extension>
		</scope>
		<extension>{core}extensions/session/</extension>
		<extension>{core}extensions/logger/</extension>
		<extension>{core}extensions/html/</extension>
		<extension>{core}extensions/i8n/</extension>
		<extension>{core}extensions/resources/</extension>
		<extension>{core}extensions/mvc/</extension>
		<scope mode="development">
			<extension>{core}extensions/docs/</extension>
			<extension>{core}extensions/blackmore/</extension>
		</scope>
		<!-- <extension>{core}extensions/oauth/</extension> -->
	</extensionList>
</scabbia>