<?php
	use Scabbia\Extensions\Views\views;
	use Scabbia\Extensions\Session\session;
	use Scabbia\Extensions\Blackmore\blackmore;
	use Scabbia\framework;
?>
<?php views::viewFile('{vendor}scabbia/scabbia/views/blackmore/header.php'); ?>
<table id="pageMiddleTable">
	<tr>
		<td id="pageMiddleSidebar">
			<div class="middleLine">
				<?php if(isset($error)) { ?>
				<div class="message errormsg">
					<p><?php echo $error; ?></p>
				</div>
				<?php } ?>

				<?php if(session::existsFlash('notification')) { ?>
				<div class="message info">
					<p><?php echo session::getFlash('notification'); ?></p>
				</div>
				<?php } ?>

				<div class="menuDivContainer">
					<?php views::viewFile('{vendor}scabbia/scabbia/views/blackmore/sectionMenu.php', blackmore::$module); ?>
				</div>
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

				<table class="fullWidth">
					<tbody>
					<tr>
						<td class="halfWidth">
							<h3><?php echo _('Framework Debug:'); ?></h3>

							<div id="placeholder">

								<?php
								$tPrevious = framework::$timestamp;
								foreach(framework::$milestones as $tMilestone) {
									echo $tMilestone[0], ' = ', number_format($tMilestone[1] - $tPrevious, 5), ' ms.<br />';
									$tPrevious = $tMilestone[1];
								}
								echo '<b>total</b> = ', number_format($tPrevious - framework::$timestamp, 5), ' ms.<br />';
								?>

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
<?php views::viewFile('{vendor}scabbia/scabbia/views/blackmore/footer.php'); ?>