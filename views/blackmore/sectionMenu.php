<?php
	use Scabbia\Extensions\Blackmore\Blackmore;
	use Scabbia\Extensions\Http\Http;

    if (isset(Blackmore::$menuItems[$model])) {
        $tMenu = Blackmore::$menuItems[$model];
?>
<ul class="nav nav-list">
	<li class="nav-header"><?php echo $tMenu[1]; ?></li>
	<?php
        foreach ($tMenu[2] as $tMenuItem) {
            if ($tMenuItem === '-') {
    ?>
        <li class="divider"></li>
    <?php
                continue;
            }
    ?>
		<li><a href="<?php echo $tMenuItem[0]; ?>"><i class="icon-<?php echo $tMenuItem[1]; ?>"></i> <?php echo $tMenuItem[2]; ?></a></li>
	<?php
        }
    ?>
</ul>
<?php
    }
?>