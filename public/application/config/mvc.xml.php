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
		<view namePattern="{@path}{@controller}/{@action}.{@extension}" defaultViewExtension="cshtml">
			<viewEngineList>
				<viewEngine extension="cshtml" class="viewengine_razor" />
			</viewEngineList>
		</view>
	</mvc>

	<phptal>
		<installation path="{core}includes/3rdparty/PHPTAL-1.2.2" />
	</phptal>
	
	<smarty>
		<installation path="{core}includes/3rdparty/Smarty-3.1.7/libs" />
	</smarty>

	<raintpl>
		<installation path="{core}includes/3rdparty/raintpl-v.2.7.1.2-0/inc" />
	</raintpl>

	<twig>
		<installation path="{core}includes/3rdparty/Twig-v1.6.0-0/lib/Twig" />
	</twig>
</scabbia>