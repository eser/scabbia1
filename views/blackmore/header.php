<?php
	use Scabbia\Extensions\Blackmore\Blackmore;
	use Scabbia\Config;
	use Scabbia\Framework;
	use Scabbia\Extensions\Html\Html;
	use Scabbia\Extensions\Http\Http;
?>
<?php echo Html::doctype('html5'); ?>
<html lang="en-us">
	<head>
		<meta charset="utf-8" />

		<title><?php echo _(Config::get('blackmore/title', 'Scabbia: Blackmore')); ?></title>

		<link type="text/css" href="<?php echo Http::url('scabbia.css?core,jquery,validation,bootstrap,cleditor,tablesorter,blackmore'); ?>" rel="stylesheet" media="all" />
		<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo Http::url('home/rss'); ?>" />
		<link rel="pingback" href="<?php echo Http::url('api/xmlrpc'); ?>" />

		<script type="text/javascript" src="<?php echo Http::url('scabbia.js?core,jquery,validation,bootstrap,cleditor,tablesorter,flot,blackmore'); ?>"></script>
	</head>
	<body class="<?php echo Config::get('blackmore/bodyStyle', 'stretch'); ?>">
		<script type="text/javascript">
			$l.contentBegin('main', '<?php echo Framework::$siteroot; ?>');
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
						<a href="<?php echo Http::url('blackmore/index'); ?>"><img src="<?php echo $root, Config::get('blackmore/logo', '/scabbia/blackmore/images/logo.png'); ?>" alt="" /></a>
					</div>
				</div>
				<div id="pageTopMenu">
					<div class="containerBox inner">
						<nav>
                            <div id="pageTopNavbar" class="navbar navbar-static">
                                <div class="navbar-inner">
                                    <div class="container" style="width: auto;">
                                        <!-- <a class="brand" href="#">Blackmore</a> -->
                                        <ul class="nav" role="navigation">
                                        <?php foreach(Blackmore::$modules as $tKey => $tModule) { ?>
                                            <?php if(isset($tModule['submenus']) && $tModule['submenus']) { ?>
                                                <li class="dropdown">
                                                    <a href="<?php echo Http::url('blackmore/' . $tKey); ?>" role="button" class="dropdown-toggle" data-toggle="dropdown">
                                                        <?php echo _($tModule['title']); ?>
                                                        <b class="caret"></b>
                                                    </a>
                                                    <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                                                    <?php
                                                    foreach($tModule['actions'] as $tSubmenuItem) {
                                                        if(!isset($tSubmenuItem['menutitle'])) {
                                                            continue;
                                                        }
                                                        $tIcon = isset($tSubmenuItem['icon']) ? $tSubmenuItem['icon'] : 'minus';
                                                    ?>
                                                        <li role="presentation">
                                                            <a role="menuitem" tabindex="-1" href="<?php echo Http::url('blackmore/' . $tKey . '/' . $tSubmenuItem['action']); ?>"><i class="icon-<?php echo $tIcon; ?>"></i> <?php echo _($tSubmenuItem['menutitle']); ?></a>
                                                        </li>
                                                    <?php } ?>
                                                    </ul>
                                                </li>
                                            <?php } ?>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
						</nav>
						<div class="clear"></div>
					</div>
				</div>
			</header>
			<section id="pageMiddle">
				<div class="wrapper">
					<div class="inner">
