<?php
use Scabbia\Extensions\Html\Html;
use Scabbia\Extensions\Http\Http;
use Scabbia\Framework;

?>
<?php echo Html::doctype('html5'); ?>
<html lang="en-US">
<head>
	<meta charset="utf-8" />

	<title><?php echo _('Scabbia Framework: Docs'); ?></title>
	<link rel="shortcut icon" href="<?php echo $root; ?>/scabbia/favicon.ico" type="image/x-icon" />

	<link type="text/css" href="<?php echo Http::url('scabbia.css?core,docs'); ?>" rel="stylesheet" media="all" />
	<script type="text/javascript" src="<?php echo Http::url('scabbia.js?core'); ?>"></script>
</head>
<body>
<script type="text/javascript">
	$l.contentBegin('main', '<?php echo Framework::$siteroot; ?>');
</script>

<div id="page">
	<header id="pageTop">
		Scabbia Documentation
	</header>
	<section id="pageMiddle">
