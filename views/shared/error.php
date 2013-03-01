<?php
    use Scabbia\Extensions\Html\Html;
    use Scabbia\Extensions\Http\Http;
    use Scabbia\Framework;

	echo Html::doctype('html5');
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US">
<head>
	<title>Scabbia Framework</title>
	<meta charset="utf-8" />

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