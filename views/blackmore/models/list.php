<?php
    use Scabbia\Extensions\Blackmore\Blackmore;
    use Scabbia\Extensions\Http\Http;
    use Scabbia\Extensions\Session\Session;
    use Scabbia\Extensions\Views\Views;
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

				<h2 class="iconxcategories"><?php echo _($module['title']); ?></h2>

				<table class="tablesorter">
					<thead>
						<tr>
					<?php foreach($fields as $field) { ?>
							<th><?php echo _($field['title']); ?></th>
					<?php } ?>
							<th></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach($rows as $row) { ?>
						<tr>
					<?php foreach($row as $cell) { ?>
							<td><?php echo $cell; ?></td>
					<?php } ?>
							<td>
								<a class="iconcategoryedit"
										href="<?php echo Http::url('blackmore/' . $module['name'] . '/edit/' . $row['slug']); ?>"><?php echo _('Edit'); ?></a>
								<a class="iconcategorydelete delete"
										href="<?php echo Http::url('blackmore/' . $module['name'] . '/remove/' . $row['slug']); ?>"><?php echo _('Remove'); ?></a>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<div id="tablepager">
					<form>
						<img src="<?php echo Http::url('scabbia/jquery.tablesorter/images/first.png'); ?>" class="first" />
						<img src="<?php echo Http::url('scabbia/jquery.tablesorter/images/prev.png'); ?>" class="prev" />
						<input type="text" class="pagedisplay" readonly="readonly" />
						<img src="<?php echo Http::url('scabbia/jquery.tablesorter/images/next.png'); ?>" class="next" />
						<img src="<?php echo Http::url('scabbia/jquery.tablesorter/images/last.png'); ?>" class="last" />
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
<?php Views::viewFile('{core}views/blackmore/footer.php'); ?>