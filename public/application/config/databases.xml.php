<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<databaseList>
		<database id="dbconn" default="default">
			<persistent />
			<overrideCase>natural</overrideCase>
			<scope mode="development">
				<pdoString>mysql:host=localhost;dbname=test</pdoString>
				<username>root</username>
				<password>passwd</password>
				<initCommand>
					SET NAMES 'utf8'
				</initCommand>
			</scope>
			<scope mode="production">
				<pdoString>pgsql:host=localhost;port=5432;dbname=test</pdoString>
				<username>postgres</username>
				<password>passwd</password>
				<initCommand />
			</scope>
		</database>
	</databaseList>
</scabbia>