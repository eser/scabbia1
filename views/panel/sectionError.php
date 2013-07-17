<?php
use Scabbia\Extensions\Session\Session;

?>
<?php
    if (($notification = Session::getFlash('notification')) !== null) {
?>
    <div class="alert alert-<?php echo $notification[0]; ?>">
        <i class="icon-<?php echo $notification[1]; ?>"></i> <?php echo implode('<br />', (array)$notification[2]); ?>
    </div>
<?php } ?>
