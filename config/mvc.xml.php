<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<mvc>
		<routes>
			<notfoundController>home</notfoundController>
			<notfoundAction>notfound</notfoundAction>
			<controllerUrlKey>0</controllerUrlKey>
			<defaultController>home</defaultController>
			<actionUrlKeys>1</actionUrlKeys>
			<defaultAction>index</defaultAction>
			<link>{@siteroot}/{@controller}/{@action}{@parameters}{@queryString}</link>
		</routes>

		<!-- _{@device}_{@language} -->
		<view>
			<namePattern>{@path}{@controller}/{@action}.{@extension}</namePattern>
			<defaultViewExtension>php</defaultViewExtension>
			<viewEngineList>
				<scope mode="development">
					<viewEngine>
						<extension>md</extension>
						<class>viewengine_markdown</class>
					</viewEngine>
				</scope>
			</viewEngineList>
		</view>
	</mvc>
</scabbia>