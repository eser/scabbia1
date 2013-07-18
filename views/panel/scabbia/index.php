<?php
use Scabbia\Extensions\Panel\Controllers\Panel;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\I18n\I18n;
use Scabbia\Extensions\Session\Session;
use Scabbia\Extensions\Views\Views;

?>
<?php Views::viewFile('{core}views/panel/header.php'); ?>
<table id="pageMiddleTable">
	<tr>
		<td id="pageMiddleSidebar">
            <div class="menuDivContainer">
                <?php Views::viewFile('{core}views/panel/sectionError.php', Panel::$module); ?>
                <?php Views::viewFile('{core}views/panel/sectionMenu.php', Panel::$module); ?>
            </div>
			<div class="clearfix"></div>
		</td>
		<td id="pageMiddleSidebarToggle">
			&laquo;
		</td>
		<td id="pageMiddleContent">
			<div class="topLine"></div>
			<div class="middleLine">

				<h2 class="iconxdashboard"><?php echo I18n::_('Dashboard'); ?></h2>

                <div class="row-fluid">
                <?php
                    foreach (Panel::$menuItems as $tKey => $tMenu) {
                        if ($tKey === 'index') {
                            continue;
                        }
                ?>
                    <div class="span8">
                    <h3>
                        <a href="<?php echo $tMenu[Panel::MENU_TITLEURL]; ?>">
                        <?php echo $tMenu[Panel::MENU_TITLE]; ?>
                        </a>
                    </h3>
                    <ul class="nav nav-pills nav-stacked">
                        <?php
                        foreach ($tMenu[Panel::MENU_ITEMS] as $tMenuItem) {
                            if ($tMenuItem === '-') {
                                ?>
                                <li class="divider"></li>
                                <?php
                                continue;
                            }
                            ?>
                            <li>
                                <a href="<?php echo $tMenuItem[Panel::MENUITEM_URL]; ?>"><i class="icon-<?php echo $tMenuItem[Panel::MENUITEM_ICON]; ?>"></i> <?php echo $tMenuItem[Panel::MENUITEM_TITLE]; ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                    </div>
                <?php } ?>
            </div>

			</div>
			<div class="bottomLine"></div>
			<div class="clearfix"></div>
		</td>
		<td id="pageMiddleExtra">
		</td>
	</tr>
</table>
<?php Views::viewFile('{core}views/panel/footer.php'); ?>