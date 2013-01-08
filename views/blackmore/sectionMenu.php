<?php
	$tModule = & blackmore::$modules[$model];
?>
<div class="menuDiv">
	<div class="menuDivHeader"><a class="boxed"
			href="<?php echo mvc::url('blackmore/' . $model); ?>"><?php echo _($tModule['title']); ?></a>
	</div>
	<?php if(isset($tModule['submenus']) && $tModule['submenus']) { ?>
	<ul>
		<?php
		foreach($tModule['actions'] as $tSubmenuItem) {
			if(!isset($tSubmenuItem['menutitle'])) {
				continue;
			}
			?>
			<li><a class="boxed iconcategoryadd"
					href="<?php echo mvc::url('blackmore/' . $model . '/' . $tSubmenuItem['action']); ?>"><?php echo _($tSubmenuItem['menutitle']); ?></a>
			</li>
			<?php } ?>
	</ul>
	<?php } ?>
</div>

