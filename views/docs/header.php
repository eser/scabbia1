<?php
	use Scabbia\Extensions\Html\Html;
	use Scabbia\Extensions\Http\Http;
	use Scabbia\Framework;
?>
<?php echo Html::doctype('html5'); ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
<head>
	<meta charset="utf-8" />

	<title><?php echo _('Scabbia: Docs'); ?></title>

	<link type="text/css" href="<?php echo Http::url('scabbia.css?reset,docs'); ?>" rel="stylesheet" media="all" />
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo Http::url('rss'); ?>" />
	<link rel="pingback" href="<?php echo Http::url('xmlrpc'); ?>" />

	<script type="text/javascript" src="<?php echo Http::url('scabbia.js?docs'); ?>"></script>
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
