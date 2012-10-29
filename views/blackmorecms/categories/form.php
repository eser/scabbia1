<?php mvc::viewFile('{core}views/blackmore/header.php'); ?>
			<form method="POST" action="@mvc::url('editor/category')">
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
										<div class="menuDivHeader"><a class="boxed" href="#"><?php echo _('Category'); ?></a></div>
										<?php echo $inputId; ?>

										<p>
											<?php echo _('Type:'); ?>
											<?php echo $inputType; ?>
										</p>

										<p>
											<?php echo _('Name:'); ?>
											<?php echo $inputName; ?>
										</p>

										<p>
											<?php echo _('Slug:'); ?>
											<?php echo $inputSlug; ?>
										</p>

										<p>
											<input type="submit" class="submit" value="<?php echo _('Submit'); ?>" />
										</p>
									</div>
								</div>
							</div>
							<div class="bottomLine"></div>
							<div class="clear"></div>
						</td>
						<td id="pageMiddleSidebarToggle">
							&laquo;
						</td>
						<td id="pageMiddleContent">
							<div class="topLine"></div>
							<div class="middleLine">

							</div>
							<div class="bottomLine"></div>
							<div class="clear"></div>
						</td>
						<td id="pageMiddleExtra">
						</td>
					</tr>
				</table>
			</form>
<?php mvc::viewFile('{core}views/blackmore/footer.php'); ?>