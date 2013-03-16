<?php
	use Scabbia\Extensions\Views\Views;
	use Scabbia\Extensions\Session\Session;
?>
<?php Views::viewFile('{core}views/blackmore/header.php'); ?>
	<form method="POST" action="<?php echo $postback; ?>">
		<table id="pageMiddleTable">
			<tr>
				<td id="pageMiddleSidebar">
                    <div class="menuDivContainer">
                        <?php if(isset($error)) { ?>
                            <div class="alert alert-error">
                                <?php echo $error; ?>
                            </div>
                        <?php } ?>

                        <?php if(Session::existsFlash('notification')) {
                            $notification = Session::getFlash('notification'); ?>
                            <div class="alert alert-info">
                                <i class="icon-<?php echo $notification[0]; ?>"></i> <?php echo $notification[1]; ?>
                            </div>
                        <?php } ?>

                        <div class="menuDiv">
                            <fieldset>
                                <legend><?php echo _($module['singularTitle']); ?></legend>
                                <?php
                                foreach($fields as $tField) {
                                    echo $tField['html'];
                                }
                                ?>
                                <input type="submit" class="btn btn-primary pull-right" value="<?php echo _('Submit'); ?>" />
                            </fieldset>
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

					</div>
					<div class="bottomLine"></div>
					<div class="clear"></div>
				</td>
				<td id="pageMiddleExtra">
				</td>
			</tr>
		</table>
	</form>
<?php Views::viewFile('{core}views/blackmore/footer.php'); ?>