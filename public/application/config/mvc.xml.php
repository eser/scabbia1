<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<mvc autorun="1">
		<routing
			defaultController="home" defaultAction="index"
			notfoundController="home" notfoundAction="notfound"
			controllerUrlKey="0" actionUrlKey="1" />

		<!-- _{@device}_{@language} -->
		<view	namePattern="{@controller}_{@action}{@extension}"
				defaultExtension=".cshtml"
			/>
	</mvc>
</scabbia>