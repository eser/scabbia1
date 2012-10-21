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
						<a href="<?php echo mvc::url('editor/index'); ?>"><img src="<?php echo $root; ?>/res/css/images/logocube.png" width="400" height="113" alt="" /></a>
					</div>
				</div>
				<div id="pageTopMenu">
					<div class="containerBox">
						<nav>
							<ul class="noMargin">
								<li class="memu-root floatLeft"><a class="memu-caption memu-item" href="<?php echo mvc::url('editor/index'); ?>"><?php echo _('Dashboard'); ?></a></li>
								<li class="memu-root floatLeft"><a class="memu-caption memu-item" href="<?php echo mvc::url('editor/categories'); ?>"><?php echo _('Categories'); ?></a>
									<ul class="memu-submenu noMargin">
										<li class="memu-item"><a class="boxed" href="<?php echo mvc::url('editor/category'); ?>"><?php echo _('New Category'); ?></a></li>
										<li class="memu-item"><a class="boxed" href="<?php echo mvc::url('editor/categories'); ?>"><?php echo _('All Categories'); ?></a></li>
									</ul>
								</li>
								<li class="memu-root floatLeft"><a class="memu-caption memu-item" href="<?php echo mvc::url('editor/posts'); ?>"><?php echo _('Posts'); ?></a>
									<ul class="memu-submenu noMargin">
										<li class="memu-item"><a class="boxed" href="<?php echo mvc::url('editor/post'); ?>"><?php echo _('New Post'); ?></a></li>
										<li class="memu-item"><a class="boxed" href="<?php echo mvc::url('editor/posts'); ?>"><?php echo _('All Posts'); ?></a></li>
										<?php if(isset($menuCategories['post'])) { ?>
											<?php foreach($menuCategories['post'] as $tCategory) { ?>
												<li class="memu-item"><a class="boxed" href="<?php echo mvc::url('editor/posts/' . $tCategory['categoryid']); ?>"><?php echo $tCategory['name']; ?></a></li>
											<?php } ?>
										<?php } ?>
									</ul>
								</li>
								<li class="memu-root floatLeft"><a class="memu-caption memu-item" href="<?php echo mvc::url('editor/pages'); ?>"><?php echo _('Pages'); ?></a>
									<ul class="memu-submenu noMargin">
										<li class="memu-item"><a class="boxed" href="<?php echo mvc::url('editor/page'); ?>"><?php echo _('New Page'); ?></a></li>
										<li class="memu-item"><a class="boxed" href="<?php echo mvc::url('editor/pages'); ?>"><?php echo _('All Pages'); ?></a></li>
										<?php if(isset($menuCategories['page'])) { ?>
											<?php foreach($menuCategories['page'] as $tCategory) { ?>
												<li class="memu-item"><a class="boxed" href="<?php echo mvc::url('editor/pages/' . $tCategory['categoryid']); ?>"><?php echo $tCategory['name']; ?></a></li>
											<?php } ?>
										<?php } ?>
									</ul>
								</li>
								<li class="memu-root floatLeft"><a class="memu-caption memu-item" href="<?php echo mvc::url('editor/options'); ?>"><?php echo _('Options'); ?></a></li>
								<li class="memu-root floatLeft"><a class="memu-caption memu-item" href="<?php echo mvc::url('user/login'); ?>"><?php echo _('Logout'); ?></a></li>
							</ul>
						</nav>
						<div class="clear"></div>
					</div>
				</div>
			</header>
			<section id="pageMiddle">
