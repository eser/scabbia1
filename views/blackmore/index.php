<?php
	use Scabbia\Extensions\Views\Views;
	use Scabbia\Extensions\Session\Session;
	use Scabbia\Extensions\Blackmore\Blackmore;
	use Scabbia\Extensions\Http\Http;
?>
<?php Views::viewFile('{core}views/blackmore/header.php'); ?>
<table id="pageMiddleTable">
	<tr>
		<td id="pageMiddleSidebar">
			<div class="middleLine">
				<div class="menuDivContainer">
                    <?php if(isset($error)) { ?>
                        <div class="alert alert-error">
                            <?php echo $error; ?>
                        </div>
                    <?php } ?>

                    <?php if(Session::existsFlash('notification')) {
                        $notification = Session::getFlash('notification'); ?>
                        <div class="alert alert-info">
                            <i class="icon-<?php echo $notification[0]; ?>"></i> <?php echo $notification[1]; ?>
                        </div>
                    <?php } ?>

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
<?php Views::viewFile('{core}views/blackmore/footer.php'); ?>