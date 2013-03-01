<?php
	use Scabbia\Config;
	use Scabbia\Framework;
	use Scabbia\Extensions\Html\Html;
	use Scabbia\Extensions\Http\Http;
?>
<?php echo Html::doctype('html5'); ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
<head>
	<meta charset="utf-8" />

	<title><?php echo _(Config::get('blackmore/title', 'Scabbia: Blackmore')); ?></title>

	<link type="text/css" href="<?php echo Http::url('scabbia.css?reset,jquery,jqueryui,validation,cleditor,tablesorter,shadowbox,blackmore') ?>" rel="stylesheet" media="all" />
    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo Http::url('home/rss'); ?>" />
    <link rel="pingback" href="<?php echo Http::url('api/xmlrpc'); ?>" />

	<script type="text/javascript" src="<?php echo Http::url('scabbia.js?jquery,jqueryui,validation,cleditor,tablesorter,shadowbox,flot,blackmore'); ?>"></script>
</head>
<body class="<?php echo Config::get('blackmore/bodyStyle', 'stretch'); ?> login">
<script type="text/javascript">
	$l.contentBegin('login', '<?php echo Framework::$siteroot; ?>');
</script>

<div id="page">
	<div class="block small center login">

		<div class="block_head">
			<div class="bheadl"></div>
			<div class="bheadr"></div>

			<h2><?php echo _(Config::get('blackmore/loginTitle', 'Scabbia: Blackmore Login')); ?></h2>
		</div>

		<div class="block_content">
			<?php if(isset($error)) { ?>
			<h2><?php echo _('Error:'); ?></h2>
			<div class="error"><?php echo $error; ?></div>
			<?php } ?>

			<form method="POST" action="<?php echo Http::url('blackmore/login'); ?>">
				<p>
					<label><?php echo _('Username:'); ?></label> <br />
					<input type="text" class="text tipsyFocus" name="username" title="Enter username" />
				</p>

				<p>
					<label><?php echo _('Password:'); ?></label> <br />
					<input type="password" class="text tipsyFocus" name="password" title="Enter password" />
				</p>

				<p>
					<input type="submit" class="submit" value="<?php echo _('Login'); ?>" name="submit" />
				</p>
			</form>

		</div>

		<div class="bendl"></div>
		<div class="bendr"></div>

	</div>
</div>

<script type="text/javascript">
	$l.contentEnd();
</script>
</body>
</html>