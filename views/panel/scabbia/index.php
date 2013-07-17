<?php
use Scabbia\Extensions\Panel\Controllers\Panel;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\I18n\I18n;
use Scabbia\Extensions\Session\Session;
use Scabbia\Extensions\Views\Views;

?>
<?php Views::viewFile('{core}views/panel/header.php'); ?>
<table id="pageMiddleTable">
	<tr>
		<td id="pageMiddleSidebar">
            <div class="menuDivContainer">
                <?php Views::viewFile('{core}views/panel/sectionError.php', Panel::$module); ?>
                <?php Views::viewFile('{core}views/panel/sectionMenu.php', Panel::$module); ?>
            </div>
			<div class="clear"></div>
		</td>
		<td id="pageMiddleSidebarToggle">
			&laquo;
		</td>
		<td id="pageMiddleContent">
			<div class="topLine"></div>
			<div class="middleLine">

				<h2 class="iconxdashboard"><?php echo I18n::_('Scabbia'); ?></h2>

				<table class="fullWidth valignTop">
					<tbody>
					<tr>
						<td class="halfWidth">
							<h3><?php echo I18n::_('Framework Directives:'); ?></h3>

							<div id="placeholder">

								* model generator<br />
								* edit configuration files<br />
								* edit .htaccess/web.config<br />
								* add/remove/download extensions<br />
								* add/remove downloads<br />
								* edit database<br />
								* edit files<br />
								* <a href="<?php echo Http::url('panel/build'); ?>">build</a><br />
								* <a href="<?php echo Http::url('panel/purge'); ?>">purge</a><br />

							</div>
							<div class="clear"></div>
						</td>
						<td class="halfWidth">
							<h3><?php echo I18n::_('Statistics:'); ?></h3>

							<div id="placeholderVisitors"></div>
							<div class="clear"></div>
						</td>
					</tr>
					</tbody>
				</table>

			</div>
			<div class="bottomLine"></div>
			<div class="clear"></div>
		</td>
		<td id="pageMiddleExtra">
		</td>
	</tr>
</table>
<?php Views::viewFile('{core}views/panel/footer.php'); ?>