<?php
use Scabbia\Extensions\Blackmore\Blackmore;
use Scabbia\Extensions\Http\Http;

?>
<?php
    if (isset(Blackmore::$menuItems[$model])) {
        $tMenu = Blackmore::$menuItems[$model];
?>
<ul class="nav nav-list">
	<li class="nav-header"><?php echo $tMenu[Blackmore::MENU_TITLE]; ?></li>
	<?php
        foreach ($tMenu[Blackmore::MENU_ITEMS] as $tMenuItem) {
            if ($tMenuItem === '-') {
    ?>
        <li class="divider"></li>
    <?php
                continue;
            }
    ?>
		<li><a href="<?php echo $tMenuItem[Blackmore::MENUITEM_URL]; ?>"><i class="icon-<?php echo $tMenuItem[Blackmore::MENUITEM_ICON]; ?>"></i> <?php echo $tMenuItem[Blackmore::MENUITEM_TITLE]; ?></a></li>
	<?php
        }
    ?>
</ul>
<?php
    }
?>