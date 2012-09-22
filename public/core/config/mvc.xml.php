<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<mvc autorun="1">
		<routes
			notfoundController="home" notfoundAction="notfound"
			controllerUrlKey="0" defaultController="home"
			actionUrlKeys="1" defaultAction="index"
			link="{@siteroot}/{@controller}/{@action}{@parameters}{@queryString}"
			/>

		<!-- _{@device}_{@language} -->
		<view namePattern="{@path}{@controller}/{@action}.{@extension}" defaultViewExtension="php">
			<viewEngineList>
				<viewEngine extension="md" class="viewengine_markdown" />
			</viewEngineList>
		</view>
	</mvc>
</scabbia>