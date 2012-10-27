<?php mvc::viewFile('{core}views/blackmore/header.php'); ?>
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
									<div class="menuDiv">
										<div class="menuDivHeader"><a class="boxed" href="#"><?php echo _('Create'); ?></a></div>
										<ul>
											<li><a class="boxed iconcategoryadd" href="<?php echo mvc::url('editor/category'); ?>"><?php echo _('New Category'); ?></a></li>
											<li><a class="boxed iconpostadd" href="<?php echo mvc::url('editor/post'); ?>"><?php echo _('New Post'); ?></a></li>
											<li><a class="boxed iconpageadd" href="<?php echo mvc::url('editor/page'); ?>"><?php echo _('New Page'); ?></a></li>
											<li><a class="boxed iconlinkadd" href="<?php echo mvc::url('editor/link'); ?>"><?php echo _('New Link'); ?></a></li>
											<li><a class="boxed iconfileadd" href="<?php echo mvc::url('editor/file'); ?>"><?php echo _('New File'); ?></a></li>
										</ul>
									</div>
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
													* <a href="<?php echo mvc::url('blackmore/build'); ?>">build</a><br />
													* <a href="<?php echo mvc::url('blackmore/purge'); ?>">purge</a><br />

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
<?php mvc::viewFile('{core}views/blackmore/footer.php'); ?>