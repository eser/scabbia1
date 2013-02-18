<?php
	use Scabbia\Extensions\Views\views;
	use Scabbia\Extensions\Session\session;
	use Scabbia\Extensions\Blackmore\blackmore;
	use Scabbia\Extensions\Mvc\mvc;
?>
<?php views::viewFile('{core}views/blackmore/header.php'); ?>
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
					<?php views::viewFile('{core}views/blackmore/sectionMenu.php', blackmore::$module); ?>
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

				<h2 class="iconxcategories"><?php echo _($module['title']); ?></h2>

				<table class="tablesorter">
					<thead>
					<tr>
						<?php
						foreach($module['fieldList'] as $field) {
							if(!array_key_exists('list', $field['methods'])) {
								continue;
							}
							?>
							<th><?php echo _($field['title']); ?></th>
							<?php } ?>
						<th></th>
					</tr>
					</thead>
					<tbody>
					<?php foreach($rows as $row) { ?>
					<tr class="row" id="row-<?php echo $row['slug']; ?>">
						<?php
						foreach($module['fieldList'] as $field) {
							if(!array_key_exists('list', $field['methods'])) {
								continue;
							}
							?>
							<td><?php echo $row[$field['name']]; ?></td>
							<?php } ?>
						<td>
							<a class="iconcategoryedit"
									href="<?php echo mvc::url('blackmore/' . $module['name'] . '/edit/' . $row['slug']); ?>"><?php echo _('Edit'); ?></a>
							<a class="iconcategorydelete delete"
									href="<?php echo mvc::url('blackmore/' . $module['name'] . '/remove/' . $row['slug']); ?>"><?php echo _('Remove'); ?></a>
						</td>
					</tr>
						<?php } ?>
					</tbody>
				</table>
				<div id="tablepager">
					<form>
						<img src="<?php echo $root; ?>/scabbia/tablesorter/images/first.png" class="first" />
						<img src="<?php echo $root; ?>/scabbia/tablesorter/images/prev.png" class="prev" />
						<input type="text" class="pagedisplay" readonly="readonly" />
						<img src="<?php echo $root; ?>/scabbia/tablesorter/images/next.png" class="next" />
						<img src="<?php echo $root; ?>/scabbia/tablesorter/images/last.png" class="last" />
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
<?php views::viewFile('{core}views/blackmore/footer.php'); ?>