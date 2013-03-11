<?php
	use Scabbia\Extensions\Views\Views;
	use Scabbia\Extensions\Session\Session;
	use Scabbia\Extensions\Blackmore\Blackmore;
	use Scabbia\Framework;
?>
<?php Views::viewFile('{core}views/blackmore/header.php'); ?>
<table id="pageMiddleTable">
	<tr>
		<td id="pageMiddleSidebar">
			<div class="middleLine">
				<?php if(isset($error)) { ?>
				<div class="message errormsg">
					<p><?php echo $error; ?></p>
				</div>
				<?php } ?>

				<?php if(Session::existsFlash('notification')) { ?>
				<div class="message info">
					<p><?php echo Session::getFlash('notification'); ?></p>
				</div>
				<?php } ?>

				<div class="menuDivContainer">
					<?php Views::viewFile('{core}views/blackmore/sectionMenu.php', Blackmore::$module); ?>
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

				<table class="fullWidth valignTop">
					<tbody>
					<tr>
						<td class="halfWidth">
							<h3><?php echo _('Framework Debug:'); ?></h3>

							<div id="placeholder">

								<?php
								$tPrevious = Framework::$timestamp;
								foreach(Framework::$milestones as $tMilestone) {
									echo $tMilestone[0], ' = ', number_format($tMilestone[1] - $tPrevious, 5), ' ms.<br />';
									$tPrevious = $tMilestone[1];
								}
								echo '<b>total</b> = ', number_format($tPrevious - Framework::$timestamp, 5), ' ms.<br />';
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
<?php Views::viewFile('{core}views/blackmore/footer.php'); ?>