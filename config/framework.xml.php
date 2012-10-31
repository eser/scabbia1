<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<moduleList />

	<includeList>
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
		</scope>
		<extension>session</extension>
		<extension>logger</extension>
		<extension>html</extension>
		<extension>i8n</extension>
		<extension>resources</extension>
		<extension>mvc</extension>
		<scope mode="development">
			<extension>docs</extension>
			<extension>auth</extension>
			<extension>zmodels</extension>
			<extension>blackmore</extension>
		</scope>
		<!-- <extension>oauth</extension> -->
	</extensionList>

	<options>
		<gzip>1</gzip>
		<autoload>0</autoload>
	</options>
</scabbia>