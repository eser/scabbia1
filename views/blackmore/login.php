<?php echo html::doctype('html5'); ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
	<head>
		<meta charset="utf-8" />

		<title><?php echo _(config::get(config::MAIN, '/blackmore/title', 'Scabbia: Blackmore')); ?></title>

		<link type="text/css" href="<?php echo $root; ?>/scabbia.css?reset,jquery,jqueryui,cleditor,tablesorter,shadowbox,blackmore" rel="stylesheet" media="all" />
		<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo $root; ?>/home/rss" />
		<link rel="pingback" href="<?php echo $root; ?>/xmlrpc.php" />

		<script type="text/javascript" src="<?php echo $root; ?>/scabbia.js?jquery,jqueryui,cleditor,tablesorter,shadowbox,flot,blackmore"></script>
	</head>
	<body class="<?php echo config::get(config::MAIN, '/blackmore/bodyStyle', 'stretch'); ?>">
		<script type="text/javascript">
			$l.contentBegin('main', '<?php echo framework::$siteroot; ?>');
		</script>

		<div id="page">
			<div class="block small center login">

				<div class="block_head">
					<div class="bheadl"></div>
					<div class="bheadr"></div>

					<img src="<?php echo $root; ?>/res/css/images/logocube_black.png" style="margin: 7px 20px 0px 45px; float: left;" /> <h2><?php echo _('Administration Panel'); ?></h2>
				</div>

				<div class="block_content">
					<?php if(isset($error)){ ?>
						<h2><?php echo _('Error:'); ?></h2>
						<div class="error"><?php echo $error; ?></div>
					<?php } ?>

					<form method="POST" action="<?php echo mvc::url('blackmore/login'); ?>">
						<p>
							<label><?php echo _('Username:'); ?></label> <br />
							<input type="text" class="text" name="username"/>
						</p>

						<p>
							<label><?php echo _('Password:'); ?></label> <br />
							<input type="password" class="text" name="password"/>
						</p>

						<p>
							<input type="submit" class="submit" value="<?php echo _('Submit'); ?>" name="submit" />
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