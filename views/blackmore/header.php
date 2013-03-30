<?php
use Scabbia\Extensions\Blackmore\Blackmore;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Extensions\Html\Html;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\String\String;

?>
<?php echo Html::doctype('html5'); ?>
<html lang="en-US">
	<head>
		<meta charset="utf-8" />

		<title><?php echo _(Config::get('blackmore/title', 'Scabbia: Blackmore')); ?></title>

		<link type="text/css" href="<?php echo Http::url('scabbia.css?core,jquery,validation,bootstrap,cleditor,tablesorter,blackmore'); ?>" rel="stylesheet" media="all" />
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
						<a href="#" class="bgLink floatRight bootstrap-popover" data-toggle="popover" data-placement="bottom" data-content="<?php echo String::encodeHtml((Html::tag('a', array('href' => 'http://eser.ozvataf.com/scabbia/'), 'http://eser.ozvataf.com/scabbia/'))); ?>" title="" data-original-title="<?php echo _('Scabbia Framework'), ' ', Framework::VERSION; ?>"><?php echo _('Scabbia Framework'); ?></a>
						<div class="clear"></div>
					</div>
				</div>
				<div id="pageTopLogo">
					<div class="containerBox inner">
						<a href="<?php echo Http::url('blackmore'); ?>"><img src="<?php echo $root, Config::get('blackmore/logo', '/scabbia/blackmore/images/logo.png'); ?>" alt="" /></a>
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
                                        <?php foreach (Blackmore::$menuItems as $tKey => $tMenu) { ?>
                                            <li class="dropdown">
                                                <a href="<?php echo $tMenu[0]; ?>" role="button" class="dropdown-toggle" data-toggle="dropdown">
                                                    <?php echo $tMenu[1]; ?>
                                                    <b class="caret"></b>
                                                </a>
                                                <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                                                <?php
                                                foreach ($tMenu[2] as $tMenuItem) {
                                                    if ($tMenuItem === '-') {
                                                ?>
                                                    <li class="divider"></li>
                                                <?php
                                                        continue;
                                                    }
                                                ?>
                                                    <li role="presentation">
                                                        <a role="menuitem" tabindex="-1" href="<?php echo $tMenuItem[0]; ?>"><i class="icon-<?php echo $tMenuItem[1]; ?>"></i> <?php echo $tMenuItem[2]; ?></a>
                                                    </li>
                                                <?php } ?>
                                                </ul>
                                            </li>
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
