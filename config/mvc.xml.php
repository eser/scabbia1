<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<mvc>
		<defaultController>home</defaultController>
		<defaultAction>index</defaultAction>
		<link>{@siteroot}/{@controller}/{@action}{@params}{@query}</link>

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