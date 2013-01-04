<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<!--
	<moduleList />

	<downloadList />
	-->

	<includeList>
		<!-- <include>{core}controllers/*.php</include> -->
		<!-- <include>{core}models/*.php</include> -->
	</includeList>

	<extensionList>
		<extension>string</extension>
		<extension>mime</extension>
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
		<extension>models</extension>
		<extension>views</extension>
		<extension>mvc</extension>
        <extension>fb</extension>
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
		<!-- <siteroot>/sampleapp</siteroot> -->
	</options>

	<i8n>
		<languageList>
			<language>
				<id>en</id>
				<locale>en_US.UTF-8</locale>
				<localewin>English_United States.1252</localewin>
				<internalEncoding>UTF-8</internalEncoding>
				<name>English</name>
			</language>
			<!--
			<language>
				<id>tr</id>
				<locale>tr_TR.UTF-8</locale>
				<localewin>Turkish_Turkey.1254</localewin>
				<internalEncoding>UTF-8</internalEncoding>
				<name>Turkish</name>
			</language>
			-->
		</languageList>
	</i8n>

	<logger>
		<filename>{date|'d-m-Y'} {@category}.txt</filename>
		<line>[{date|'d-m-Y H:i:s'}] {strtoupper|@category} | {@ip} | {@message}</line>
	</logger>

	<!--
	<cache>
		<keyphase></keyphase>
		<storage>memcache://192.168.2.4:11211</storage>
	</cache>

	<smtp>
		<host>ssl://mail.messagingengine.com</host>
		<port>465</port>
		<username>eser@sent.com</username>
		<password></password>
	</smtp>
	-->
</scabbia>