<?php
use Scabbia\Extensions\Html\Html;
use Scabbia\Extensions\Http\Http;
use Scabbia\Framework;

?>
<?php echo Html::doctype('html5'); ?>
<html lang="en-US">
<head>
    <meta charset="utf-8" />

	<title><?php echo _('Scabbia Framework'); ?></title>

	<link type="text/css" href="<?php echo Http::url('scabbia.css?core,errorpages'); ?>" rel="stylesheet" media="all" />
	<script type="text/javascript" src="<?php echo Http::url('scabbia.js?core'); ?>"></script>
</head>
<body>
	<script type="text/javascript">
			$l.contentBegin('main', '<?php echo Framework::$siteroot; ?>');
	</script>

	<div class="ppopup">
		<div class="content">
			<h1><?php echo $title; ?></h1>
			<div><?php echo $message; ?></div>
		</div>
	</div>

	<script type="text/javascript">
        $l.contentEnd();
    </script>

</body>
</html>