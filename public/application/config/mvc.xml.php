<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<mvc autorun="1">
		<routes
			notfoundController="home" notfoundAction="notfound"
			controllerUrlKey="0" defaultController="home"
			actionUrlKeys="1" defaultAction="index"
			link="{@siteroot}/{@controller}/{@action}{@queryString}"
			/>

		<controllerList>
			<controller name="moderator" actionUrlKeys="1,2" />
		</controllerList>

		<!-- _{@device}_{@language} -->
		<view	namePattern="{@controller}_{@action}{@extension}"
				defaultExtension=".cshtml"
			/>
	</mvc>
</scabbia>