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
											<li><a class="boxed iconcategoryadd" href="<?php echo mvc::url('blackmore/categories/add'); ?>"><?php echo _('Add Category'); ?></a></li>
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

								<h2 class="iconxcategories"><?php echo _('Categories'); ?></h2>

								<table class="tablesorter">
									<thead>
										<tr>
											<th><?php echo _('Type'); ?></th>
											<th><?php echo _('Name'); ?></th>
											<th><?php echo _('Slug'); ?></th>
											<th><?php echo _('Date'); ?></th>
											<th></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach($categories as $category) { ?>
											<tr class="category" id="category-<?php echo $category['slug']; ?>">
												<td><?php echo $category['type']; ?></td>
												<td><a href="<?php echo mvc::url('home/category/' . $category['slug']); ?>"><?php echo $category['name']; ?></a></td>
												<td><?php echo $category['slug']; ?></td>
												<td><?php echo $category['createdate']; ?></td>
												<td>
													<a class="iconcategoryedit" href="<?php echo mvc::url('blackmore/categories/edit/' . $category['slug']); ?>"><?php echo _('Edit'); ?></a>
													<a class="iconcategorydelete delete" href="<?php echo mvc::url('blackmore/categories/remove/' . $category['slug']); ?>"><?php echo _('Remove'); ?></a>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
								<div id="tablepager">
									<form>
										<img src="<?php echo $root; ?>/scabbia/tablesorter/images/first.png" class="first"/>
										<img src="<?php echo $root; ?>/scabbia/tablesorter/images/prev.png" class="prev"/>
										<input type="text" class="pagedisplay" readonly="readonly" />
										<img src="<?php echo $root; ?>/scabbia/tablesorter/images/next.png" class="next"/>
										<img src="<?php echo $root; ?>/scabbia/tablesorter/images/last.png" class="last"/>
										<select class="pagesize">
											<option value="10">10</option>
											<option selected="selected" value="25">25</option>
											<option value="50">50</option>
											<option value="100">100</option>
										</select>
									</form>
								</div>

							</div>
							<div class="bottomLine"></div>
							<div class="clear"></div>
						</td>
						<td id="pageMiddleExtra">
						</td>
					</tr>
				</table>
<?php mvc::viewFile('{core}views/blackmore/footer.php'); ?>