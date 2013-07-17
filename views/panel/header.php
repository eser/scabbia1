<?php
use Scabbia\Extensions\Auth\Auth;
use Scabbia\Extensions\Helpers\Html;
use Scabbia\Extensions\Helpers\String;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\I18n\I18n;
use Scabbia\Extensions\Panel\Controllers\Panel;
use Scabbia\Config;
use Scabbia\Framework;

?>
<?php echo Html::doctype('html5'); ?>
<html lang="en-us">
    <head>
        <meta charset="utf-8" />

        <title><?php echo I18n::_(Config::get('panel/title', 'Scabbia: Panel')); ?></title>
        <link rel="shortcut icon" href="<?php echo Http::url('scabbia/favicon.ico'); ?>" type="image/x-icon" />

        <link type="text/css" href="<?php echo Http::url('scabbia.css?core,jquery,validation,bootstrap,cleditor,tablesorter,panel'); ?>" rel="stylesheet" media="all" />
        <script type="text/javascript" src="<?php echo Http::url('scabbia.js?core,jquery,validation,bootstrap,cleditor,tablesorter,flot,panel'); ?>"></script>
    </head>
    <body class="<?php echo Config::get('panel/bodyStyle', 'stretch'); ?>">
        <script type="text/javascript">
            $l.contentBegin('main', '<?php echo $root; ?>/');
        </script>

        <div id="page">
            <header id="pageTop" class="wrapper">
                <div id="pageTopHead">
                    <div class="containerBox padding4px inner">
                        <a href="#" class="bgLink floatLeft"><?php echo I18n::_('Notifications:'); ?> <span class="bgCount">0</span></a>
                        <span class="bgSpacer floatLeft"></span>
                        <a href="#" class="bgLink floatLeft"><?php echo I18n::_('User:'); ?> <span class="bgCount"><?php echo Auth::$user['username']; ?></span></a>
                        <a href="#" class="bgLink floatRight bootstrap-popover" data-toggle="popover" data-placement="bottom" data-content="<?php echo String::encodeHtml((Html::tag('a', array('href' => 'http://larukedi.github.com/Scabbia-Framework/'), 'http://larukedi.github.com/Scabbia-Framework/'))); ?>" title="" data-original-title="<?php echo I18n::_('Scabbia Framework'), ' ', Framework::VERSION; ?>"><?php echo I18n::_('Scabbia Framework'); ?></a>
                        <div class="clear"></div>
                    </div>
                </div>
                <div id="pageTopLogo">
                    <div class="containerBox inner">
                        <a href="<?php echo Http::url('panel'); ?>"><img src="<?php echo $root, Config::get('panel/logo', '/scabbia/panel/images/logo.png'); ?>" alt="" /></a>
                    </div>
                </div>
                <div id="pageTopMenu">
                    <div class="containerBox inner">
                        <nav>
                            <div id="pageTopNavbar" class="navbar navbar-static">
                                <div class="navbar-inner">
                                    <div class="container" style="width: auto;">
                                        <!-- <a class="brand" href="#">Panel</a> -->
                                        <ul class="nav" role="navigation">
                                        <?php foreach (Panel::$menuItems as $tKey => $tMenu) { ?>
                                            <li class="dropdown">
                                                <a href="<?php echo $tMenu[Panel::MENU_TITLEURL]; ?>" role="button" class="dropdown-toggle" data-toggle="dropdown">
                                                    <?php echo $tMenu[Panel::MENU_TITLE]; ?>
                                                    <b class="caret"></b>
                                                </a>
                                                <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                                                <?php
                                                foreach ($tMenu[Panel::MENU_ITEMS] as $tMenuItem) {
                                                    if ($tMenuItem === '-') {
                                                ?>
                                                    <li class="divider"></li>
                                                <?php
                                                        continue;
                                                    }
                                                ?>
                                                    <li role="presentation">
                                                        <a role="menuitem" tabindex="-1" href="<?php echo $tMenuItem[Panel::MENUITEM_URL]; ?>"><i class="icon-<?php echo $tMenuItem[Panel::MENUITEM_ICON]; ?>"></i> <?php echo $tMenuItem[Panel::MENUITEM_TITLE]; ?></a>
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
