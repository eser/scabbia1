<?php echo html::doctype('html5'); ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
	<head>
		<meta charset="utf-8" />

		<title><?php echo _('Scabbia: Docs'); ?></title>

		<link type="text/css" href="<?php echo http::url('scabbia.css?reset,docs'); ?>" rel="stylesheet" media="all" />
		<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo http::url('rss'); ?>" />
		<link rel="pingback" href="<?php echo http::url('xmlrpc'); ?>" />

		<script type="text/javascript" src="<?php echo http::url('scabbia.js?docs'); ?>"></script>
	</head>
	<body>
		<script type="text/javascript">
			$l.contentBegin('main', '<?php echo framework::$siteroot; ?>');
		</script>

		<div id="page">
			<header id="pageTop">
				Scabbia Documentation
			</header>
			<section id="pageMiddle">
