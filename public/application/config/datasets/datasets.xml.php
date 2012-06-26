<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<datasetList>
		<dataset id="getUsers" cacheLife="60" parameters="offset,limit">
			SELECT * FROM users OFFSET {offset} LIMIT {limit}
		</dataset>
	</datasetList>
</scabbia>