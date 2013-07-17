<?php
use Scabbia\Extensions\Panel\Controllers\Panel;
use Scabbia\Extensions\Http\Http;

?>
<?php
    if (isset(Panel::$menuItems[$model])) {
        $tMenu = Panel::$menuItems[$model];
?>
<ul class="nav nav-list">
	<li class="nav-header"><?php echo $tMenu[Panel::MENU_TITLE]; ?></li>
	<?php
        foreach ($tMenu[Panel::MENU_ITEMS] as $tMenuItem) {
            if ($tMenuItem === '-') {
    ?>
        <li class="divider"></li>
    <?php
                continue;
            }
    ?>
		<li><a href="<?php echo $tMenuItem[Panel::MENUITEM_URL]; ?>"><i class="icon-<?php echo $tMenuItem[Panel::MENUITEM_ICON]; ?>"></i> <?php echo $tMenuItem[Panel::MENUITEM_TITLE]; ?></a></li>
	<?php
        }
    ?>
</ul>
<?php
    }
?>