<?php
	use Scabbia\Extensions\Blackmore\Blackmore;
	use Scabbia\Extensions\Http\http;
?>
<?php
	$tModule = & Blackmore::$modules[$model];
?>
<ul class="nav nav-list">
	<li class="nav-header"><?php echo _($tModule['title']); ?></li>
	<?php
        if(isset($tModule['submenus']) && $tModule['submenus']) {
		    foreach($tModule['actions'] as $tSubmenuItem) {
			    if(!isset($tSubmenuItem['menutitle'])) {
    				continue;
    			}
    ?>
		<li><a href="<?php echo Http::url('blackmore/' . $model . '/' . $tSubmenuItem['action']); ?>"><?php echo _($tSubmenuItem['menutitle']); ?></a></li>
	<?php
            }
        }
    ?>
</ul>

