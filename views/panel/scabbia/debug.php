<?php
use Scabbia\Extensions\Views\Views;
use Scabbia\Extensions\Session\Session;
use Scabbia\Extensions\Panel\Controllers\Panel;
use Scabbia\Framework;

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

				<h2 class="iconxdashboard"><?php echo _('Dashboard'); ?></h2>

				<table class="fullWidth valignTop">
					<tbody>
					<tr>
						<td class="halfWidth">
							<h3><?php echo _('Framework Debug:'); ?></h3>

							<div id="placeholder">

							</div>
							<div class="clear"></div>
						</td>
						<td class="halfWidth">
							<h3><?php echo _('Statistics:'); ?></h3>

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