<?php echo html::doctype('html5'); ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
	<head>
		<meta charset="utf-8" />

		<title><?php echo _(config::get('/blackmore/title', 'Scabbia: Blackmore')); ?></title>

		<link type="text/css" href="<?php echo $root; ?>/scabbia.css?reset,jquery,jqueryui,cleditor,tablesorter,shadowbox,tipsy,blackmore" rel="stylesheet" media="all" />
		<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo $root; ?>/home/rss" />
		<link rel="pingback" href="<?php echo $root; ?>/xmlrpc.php" />

		<script type="text/javascript" src="<?php echo $root; ?>/scabbia.js?jquery,jqueryui,cleditor,tablesorter,shadowbox,tipsy,flot,blackmore"></script>
	</head>
	<body class="<?php echo config::get('/blackmore/bodyStyle', 'stretch'); ?> login">
		<script type="text/javascript">
			$l.contentBegin('main', '<?php echo framework::$siteroot; ?>');
		</script>

		<div id="page">
			<div class="block small center login">

				<div class="block_head">
					<div class="bheadl"></div>
					<div class="bheadr"></div>

					<h2><?php echo _(config::get('/blackmore/loginTitle', 'Scabbia: Blackmore Login')); ?></h2>
				</div>

				<div class="block_content">
					<?php if(isset($error)){ ?>
						<h2><?php echo _('Error:'); ?></h2>
						<div class="error"><?php echo $error; ?></div>
					<?php } ?>

					<form method="POST" action="<?php echo mvc::url('blackmore/login'); ?>">
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