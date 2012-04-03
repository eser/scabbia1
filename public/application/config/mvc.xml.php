<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<mvc>
		<routing
			defaultController="home" defaultAction="index"
			notfoundController="home" notfoundAction="notfound"
			controllerUrlKey="1" actionUrlKey="2" />

		<view	namePattern="{@controller}_{@action}_{@device}_{@language}{@extension}"
				defaultExtension=".cshtml"
			/>
	</mvc>
</scabbia>