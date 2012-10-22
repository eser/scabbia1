<?php echo html::doctype('html5'); ?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-US">
	<head>
		<meta charset="utf-8" />

		<title><?php echo _('Scabbia: Blackmore'); ?></title>

		<link type="text/css" href="<?php echo $root; ?>/scabbia.css?reset,jquery,jqueryui,cleditor,tablesorter,shadowbox,blackmore" rel="stylesheet" media="all" />
		<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo $root; ?>/home/rss" />
		<link rel="pingback" href="<?php echo $root; ?>/xmlrpc.php" />

		<script type="text/javascript" src="<?php echo $root; ?>/scabbia.js?jquery,jqueryui,cleditor,tablesorter,shadowbox,flot,blackmore"></script>
	</head>
	<body>
		<script type="text/javascript">
			$l.contentBegin('main', '<?php echo framework::$siteroot; ?>');
		</script>

		<div id="page">
			<header id="pageTop">
				<div id="pageTopHead">
					<div class="containerBox padding4px">
						<a href="#" class="bgLink"><?php echo _('Notifications:'); ?> <span class="bgCount">0</span></a>
						<span class="bgSpacer"></span>
						<a href="#" class="bgLink"><?php echo _('Users:'); ?> <span class="bgCount">0</span></a>
					</div>
				</div>
				<div id="pageTopLogo">
					<div class="containerBox">
						<a href="<?php echo mvc::url('editor/index'); ?>"><img src="<?php echo $root; ?>/scabbia/blackmore/images/logo.png" width="400" height="84" alt="" /></a>
					</div>
				</div>
				<div id="pageTopMenu">
					<div class="containerBox">
						<nav>
							<ul class="noMargin">
								<?php foreach(blackmore::buildMenu() as $tMenuItem) { ?>
									<li class="memu-root floatLeft"><a class="memu-caption memu-item" href="<?php echo $tMenuItem['link']; ?>"><?php echo _($tMenuItem['title']); ?></a>
									<?php if(isset($tMenuItem['subitems'])) { ?>
									<ul class="memu-submenu noMargin">
										<?php foreach($tMenuItem['subitems'] as $tSubmenuItem) { ?>
											<li class="memu-item"><a class="boxed" href="<?php echo $tSubmenuItem['link']; ?>"><?php echo _($tSubmenuItem['title']); ?></a></li>
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