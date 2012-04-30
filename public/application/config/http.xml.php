<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<http>
		<request parsingType="2" getParameters="?&amp;" getKeys="=" /> <!-- getParameters="," getKeys=":" -->
		
		<rewriteList>
			<rewrite match="(\w+)/contacts" forward="home/mvc/$1/why" />
		</rewriteList>

		<userAgents autoCheck="1">
			<platformList>
				<platform match="windows|winnt|win95|win98" name="Windows" />
				<platform match="os x|ppc mac|ppc" name="MacOS" />
				<platform match="irix|netbsd|freebsd|openbsd|bsdi|unix|sunos|linux|debian|gnu" name="Unix" />
			</platformList>

			<crawlerList>
				<crawler type="bot" match="googlebot|msnbot|slurp|yahoo|askjeeves|fastcrawler|infoseek|lycos" name="Searchbot" />
				<crawler type="browser" match="Opera" name="Opera" />
				<crawler type="browser" match="Mozilla|Firefox|Firebird|Phoenix" name="Firefox" />
				<crawler type="browser" match="MSIE|Internet Explorer" name="Internet Explorer" />
				<crawler type="browser" match="Flock" name="Flock" />
				<crawler type="browser" match="Chrome" name="Chrome" />
				<crawler type="browser" match="Shiira" name="Shiira" />
				<crawler type="browser" match="Chimera" name="Chimera" />
				<crawler type="browser" match="Camino" name="Camino" />
				<crawler type="browser" match="Netscape" name="Netscape" />
				<crawler type="browser" match="OmniWeb" name="OmniWeb" />
				<crawler type="browser" match="Safari" name="Safari" />
				<crawler type="browser" match="Konqueror" name="Konqueror" />
				<crawler type="browser" match="icab" name="iCab" />
				<crawler type="browser" match="Lynx" name="Lynx" />
				<crawler type="browser" match="Links" name="Links" />
				<crawler type="browser" match="hotjava" name="HotJava" />
				<crawler type="browser" match="amaya" name="Amaya" />
				<crawler type="browser" match="IBrowse" name="IBrowse" />
				<crawler type="mobile" match="palm|elaine" name="Palm" />
				<crawler type="mobile" match="iphone|ipod" name="iOS" />
				<crawler type="mobile" match="blackberry" name="Blackberry" />
				<crawler type="mobile" match="symbian|series60" name="SymbianOS" />
				<crawler type="mobile" match="windows ce" name="Windows CE" />
				<crawler type="mobile" match="opera mini|operamini" name="Opera Mini" />
				<crawler type="mobile" match="mobile|wireless|j2me|phone" name="Other Mobile" />
			</crawlerList>
		</userAgents>
	</http>

	<session>
		<cookie name="sessid" life="0" ipCheck="0" uaCheck="1" keyphase="test" />
	</session>

	<access>
		<maintenance mode="1" page="{app}views/static_maintenance.php" />
		<maintenanceExcludeList>
			<maintenanceExclude ip="83.66.165.43" />
			<maintenanceExclude ip="127.0.0.1" />
		</maintenanceExcludeList>

		<ipFilter page="{app}views/static_ipban.php" />
		<ipFilterList>
			<ipFilter type="deny" pattern="127.0.0.?" />
			<ipFilter type="allow" pattern="*.*.*.*" />
		</ipFilterList>
	</access>
</scabbia>