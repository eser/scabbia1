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
			<header id="pageTop" class="wrapper">
				<div id="pageTopHead">
					<div class="containerBox padding4px inner">
						<a href="#" class="bgLink floatLeft"><?php echo _('Notifications:'); ?> <span class="bgCount">0</span></a>
						<span class="bgSpacer floatLeft"></span>
						<a href="#" class="bgLink floatLeft"><?php echo _('Users:'); ?> <span class="bgCount">0</span></a>
						<a href="http://eser.ozvataf.com/scabbia/" class="bgLink floatRight"><?php echo _('Scabbia Framework'); ?></a>
						<div class="clear"></div>
					</div>
				</div>
				<div id="pageTopLogo">
					<div class="containerBox inner">
						<a href="<?php echo mvc::url('editor/index'); ?>"><img src="<?php echo $root, config::get(config::MAIN, '/blackmore/logo', '/scabbia/blackmore/images/logo.png'); ?>" width="400" height="84" alt="" /></a>
					</div>
				</div>
				<div id="pageTopMenu">
					<div class="containerBox inner">
						<nav>
							<ul class="noMargin">
								<?php foreach(blackmore::$modules as $tKey => &$tModule) { ?>
									<li class="memu-root floatLeft"><a class="memu-caption memu-item" href="<?php echo mvc::url('blackmore/' . $tKey); ?>"><?php echo _($tModule['title']); ?></a>
									<?php if(isset($tModule['submenus']) && $tModule['submenus']) { ?>
									<ul class="memu-submenu noMargin">
										<?php
											foreach($tModule['actions'] as $tSubmenuItem) {
										?>
											<li class="memu-item"><a class="boxed" href="<?php echo mvc::url('blackmore/' . $tKey . '/' . $tSubmenuItem['action']); ?>"><?php echo _($tSubmenuItem['menutitle']); ?></a></li>
										<?php } ?>
									</ul>
									<?php } ?>
								</li>
								<?php } ?>
							</ul>
						</nav>
						<div class="clear"></div>
					</div>
				</div>
			</header>
			<section id="pageMiddle">
				<div class="wrapper">
					<div class="inner">
