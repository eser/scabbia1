<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<mvc>
		<routes>
			<controllerUrlKey>0</controllerUrlKey>
			<actionUrlKeys>1</actionUrlKeys>
			<defaultController>home</defaultController>
			<defaultAction>index</defaultAction>
			<link>{@siteroot}/{@controller}/{@action}{@parameters}{@queryString}</link>
		</routes>

<!--
		<errorPages>
			<notfound>shared/notfound</notfound>
			<restriction>shared/restriction</restriction>
			<maintenance>shared/maintenance</maintenance>
			<ipban>shared/ipban</ipban>
			<error>shared/error</error>
		</errorPages>

		<controllerList />
-->

		<!-- _{@device}_{@language} -->
		<view>
			<namePattern>{@path}{@controller}/{@action}.{@extension}</namePattern>
			<defaultViewExtension>php</defaultViewExtension>
			<viewEngineList>
				<scope mode="development">
					<viewEngine>
						<extension>md</extension>
						<class>viewEngineMarkdown</class>
					</viewEngine>
				</scope>
			</viewEngineList>
		</view>
	</mvc>
</scabbia>