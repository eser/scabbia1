<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<razor>
		<templates extension=".cshtml" templatePath="{app}views" compiledPath="{app}writable/compiledViews" />
	</razor>

	<phptal>
		<installation path="{core}include/3rdparty/PHPTAL-1.2.2" />
		<templates extension=".zpt" templatePath="{app}views" compiledPath="{app}writable/compiledViews" />
	</phptal>
	
	<smarty>
		<installation path="{core}include/3rdparty/Smarty-3.1.7/libs" />
		<templates extension=".tpl" templatePath="{app}views" compiledPath="{app}writable/compiledViews" />
	</smarty>

	<raintpl>
		<installation path="{core}include/3rdparty/raintpl-v.2.7.1.2-0/inc" />
		<templates extension=".rain" templatePath="{app}views" compiledPath="{app}writable/compiledViews" />
	</raintpl>

	<twig>
		<installation path="{core}include/3rdparty/Twig-v1.6.0-0/lib/Twig" />
		<templates extension=".twig" templatePath="{app}views" compiledPath="{app}writable/compiledViews" />
	</twig>

	<markdown>
		<templates extension=".md" templatePath="{app}views" compiledPath="{app}writable/compiledViews" />
	</markdown>

	<php>
		<templates extension=".php" templatePath="{app}views" />
	</php>
</scabbia>