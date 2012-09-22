<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<repository>
		<packageList>
			<package name="viewhome" type="txt">
				<partList>
					<part type="file" path="{app}views/home/index.cshtml" />
				</partList>
			</package>
			<package name="extensions" type="phps">
				<partList>
					<part type="file" path="{core}extensions/access.php" />
					<part type="file" path="{core}extensions/fb.php" />
				</partList>
			</package>
			<package name="javascripts" type="js">
				<partList>
					<part type="file" path="{base}res/js/laroux/laroux.js" />
					<part type="file" path="{base}res/js/jquery/sizzle.js" />
					<part type="file" path="{base}res/js/jquery/jquery-1.8.1-custom.js" />
					<part type="file" class="jqueryui" path="{base}res/js/jquery/jquery.ui.core.js" />
					<part type="file" class="jqueryui" path="{base}res/js/jquery/jquery.ui.widget.js" />
					<part type="file" class="jqueryui" path="{base}res/js/jquery/jquery.ui.mouse.js" />
					<part type="file" class="jqueryui" path="{base}res/js/jquery/jquery.ui.position.js" />
					<part type="file" class="jqueryui" path="{base}res/js/jquery/jquery.ui.sortable.js" />
					<part type="file" class="jqueryui" path="{base}res/js/jquery/jquery.ui.tabs.js" />
					<part type="file" class="jqueryui" path="{base}res/js/jquery/jquery.ui.autocomplete.js" />
					<part type="file" class="jqueryui" path="{base}res/js/jquery/jquery.ui.datepicker.js" />
					<part type="file" class="jqueryui" path="{base}res/js/laroux/laroux.ui.js" />
					<part type="file" class="map" path="{base}res/js/mapbox/mapbox-0.6.5.js" />
					<part type="function" name="mvc::exportAjaxJs" />
				</partList>
			</package>
			<package name="styles.main" type="css">
				<partList>
					<part type="file" path="{base}res/css/reset.css" />
					<part type="file" class="jqueryui" path="{base}res/css/jquery/jquery.ui.core.css" />
					<part type="file" class="jqueryui" path="{base}res/css/jquery/jquery.ui.autocomplete.css" />
					<part type="file" class="jqueryui" path="{base}res/css/jquery/jquery.ui.tabs.css" />
					<part type="file" class="jqueryui" path="{base}res/css/jquery/jquery.ui.datepicker.css" />
					<part type="file" class="jqueryui" path="{base}res/css/jquery/jquery.ui.theme.css" />
					<part type="file" class="jqueryui" path="{base}res/css/laroux/laroux.ui.css" />
					<part type="file" class="map" path="{base}res/css/mapbox/mapbox.0.6.5.css" />
				</partList>
			</package>
		</packageList>
	</repository>
</scabbia>