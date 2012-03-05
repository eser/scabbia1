<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<databaseList>
		<database id="dbconn">
			<cachePath>cache/</cachePath>
			<persistent />
			<overrideCase>natural</overrideCase>
			<!--
				<pdoString>pgsql:host=localhost;port=5432;dbname=test</pdoString>
				<username>postgres</username>
				<password>paddole</password>
			-->
			<pdoString>mysql:host=localhost;dbname=sixq</pdoString>
			<username>root</username>
			<password>paddole</password>
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
			</datasetList>
		</database>
	</databaseList>
</scabbia>