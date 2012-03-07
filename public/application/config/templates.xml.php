<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<razor>
		<templates extension="cshtml" templatePath="views" compiledPath="views/compiled" />
	</razor>

	<phptal>
		<installation path="include/3rdparty/PHPTAL-1.2.2" />
		<templates extension="zpt" templatePath="views" compiledPath="views/compiled" />
	</phptal>
	
	<smarty>
		<installation path="include/3rdparty/Smarty-3.1.7/libs" />
		<templates extension="tpl" templatePath="views" compiledPath="views/compiled" />
	</smarty>

	<raintpl>
		<installation path="include/3rdparty/raintpl-v.2.7.1.2-0/inc" />
		<templates extension="rain" templatePath="views" compiledPath="views/compiled" />
	</raintpl>

	<twig>
		<installation path="include/3rdparty/Twig-v1.6.0-0/lib/Twig" />
		<templates extension="twig" templatePath="views" compiledPath="views/compiled" />
	</twig>

	<markdown>
		<templates extension="md" templatePath="views" compiledPath="views/compiled" />
	</markdown>

	<php>
		<templates extension="php" templatePath="views" />
	</php>
</scabbia>