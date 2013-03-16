<?php
    use Scabbia\Extensions\Session\Session;

    if (Session::existsFlash('notification')) {
        $notification = Session::getFlash('notification');
?>
    <div class="alert alert-<?php echo $notification[0]; ?>">
        <i class="icon-<?php echo $notification[1]; ?>"></i> <?php echo $notification[2]; ?>
    </div>
<?php } ?>
