<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<databaseList>
		<database id="dbconn">
			<cachePath>cache/</cachePath>
			<persistent />
			<overrideCase>natural</overrideCase>
			<pdoString>mysql:host=localhost;dbname=test</pdoString>
			<!--
			<pdoString>pgsql:host=localhost;port=5432;dbname=test</pdoString>
			-->
			<username>postgres</username>
			<password>passwd</password>
			<initCommand>
				SET NAMES utf8
			</initCommand>
			<datasetList>
				<dataset id="getUserCount" cacheLife="15" parameters="">
					SELECT COUNT(*) FROM users
				</dataset>
				<dataset id="getUsers" cacheLife="15" parameters="offset,limit">
					SELECT facebookid, EMail, LongName, ImgPath, Gender, Locale, UNIX_TIMESTAMP(RecDate) AS RecDate FROM users LIMIT {offset}, {limit}
				</dataset>
				<dataset id="setUserUnsubscribed" cacheLife="0" parameters="email">
					UPDATE users SET unsubscribed='1' WHERE EMail='{squote:email}' LIMIT 1
				</dataset>
				<dataset id="getLoginPassword" cacheLife="15" parameters="name">
					SELECT password FROM accounts WHERE name='{squote:name}' LIMIT 0, 1
				</dataset>
			</datasetList>
		</database>
	</databaseList>
</scabbia>