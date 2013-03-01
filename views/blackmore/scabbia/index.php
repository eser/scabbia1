<?php
	use Scabbia\Extensions\Views\Views;
	use Scabbia\Extensions\Session\Session;
	use Scabbia\Extensions\Blackmore\Blackmore;
	use Scabbia\Extensions\Http\Http;
?>
<?php Views::viewFile('{vendor}views/blackmore/header.php'); ?>
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
					<?php Views::viewFile('{vendor}views/blackmore/sectionMenu.php', Blackmore::$module); ?>
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

				<h2 class="iconxdashboard"><?php echo _('Scabbia'); ?></h2>

				<table class="fullWidth">
					<tbody>
					<tr>
						<td class="halfWidth">
							<h3><?php echo _('Framework Directives:'); ?></h3>

							<div id="placeholder">

								* model generator<br />
								* edit configuration files<br />
								* edit .htaccess/web.config<br />
								* add/remove/download extensions<br />
								* add/remove downloads<br />
								* edit database<br />
								* edit files<br />
								* <a href="<?php echo Http::url('blackmore/build'); ?>">build</a><br />
								* <a href="<?php echo Http::url('blackmore/purge'); ?>">purge</a><br />

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
<?php Views::viewFile('{vendor}views/blackmore/footer.php'); ?>