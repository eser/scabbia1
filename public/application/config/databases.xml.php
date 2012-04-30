<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<databaseList>
		<database id="dbconn" default="default" keyphase="test">
			<cachePath>{app}writable/datasetCache/</cachePath>
			<persistent />
			<overrideCase>natural</overrideCase>
			<scope mode="development">
				<pdoString>mysql:host=localhost;dbname=test</pdoString>
				<username>root</username>
				<password>passwd</password>
			</scope>
			<scope mode="production">
				<pdoString>pgsql:host=localhost;port=5432;dbname=test</pdoString>
				<username>postgres</username>
				<password>passwd</password>
			</scope>
			<initCommand>
				SET NAMES 'utf8'
			</initCommand>
			<datasetList>
				<dataset id="getUserCount" cacheLife="15" parameters="">
					SELECT COUNT(*) FROM users
				</dataset>
				<dataset id="getUsers" cacheLife="15" parameters="offset,limit">
					SELECT facebookid, EMail, LongName, ImgPath, Gender, Locale, UNIX_TIMESTAMP(RecDate) AS RecDate FROM users LIMIT {offset}, {limit}
				</dataset>
				<dataset id="getSingleUser" cacheLife="15" parameters="uuid">
					SELECT facebookid, EMail, LongName, ImgPath, Gender, Locale, UNIX_TIMESTAMP(RecDate) AS RecDate FROM users WHERE uuid='{squote:uuid}' LIMIT 0, 1
				</dataset>
				<dataset id="setUserUnsubscribed" parameters="email">
					UPDATE users SET unsubscribed='1' WHERE EMail='{squote:email}' LIMIT 1
				</dataset>
				<dataset id="getLoginPassword" cacheLife="15" parameters="name">
					SELECT password FROM accounts WHERE name='{squote:name}' LIMIT 0, 1
				</dataset>
				<dataset id="logCampaignView" parameters="userid,campaign,operation">
					INSERT INTO userrefs (userid, campaign, operation, insertdate) VALUES ('{squote:userid}', '{squote:campaign}', '{int:operation}', CURRENT_TIMESTAMP())
				</dataset>
				<dataset id="getCsvOutput" parameters="offset,limit">
					SELECT uuid, LongName, EMail FROM users LIMIT {int:offset}, {int:limit}
				</dataset>
			</datasetList>
		</database>
	</databaseList>
</scabbia>