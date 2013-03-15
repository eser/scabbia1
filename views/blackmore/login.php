<?php
	use Scabbia\Config;
	use Scabbia\Framework;
    use Scabbia\Extensions\Blackmore\Blackmore;
	use Scabbia\Extensions\Html\Html;
	use Scabbia\Extensions\Http\Http;
?>
<?php echo Html::doctype('html5'); ?>
<html lang="en-us">
    <head>
        <meta charset="utf-8" />

        <title><?php echo _(Config::get('blackmore/title', 'Scabbia: Blackmore')); ?></title>

        <link type="text/css" href="<?php echo Http::url('scabbia.css?core,jquery,validation,bootstrap,cleditor,tablesorter,blackmore') ?>" rel="stylesheet" media="all" />
        <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo Http::url('home/rss'); ?>" />
        <link rel="pingback" href="<?php echo Http::url('api/xmlrpc'); ?>" />

        <script type="text/javascript" src="<?php echo Http::url('scabbia.js?core,jquery,validation,bootstrap,cleditor,tablesorter,flot,blackmore'); ?>"></script>
    </head>
    <body class="<?php echo Config::get('blackmore/bodyStyle', 'stretch'); ?> login">
        <script type="text/javascript">
            $l.contentBegin('login', '<?php echo Framework::$siteroot; ?>');
        </script>

        <div class="block">

            <div class="block_head">
                <h2><?php echo _(Config::get('blackmore/loginTitle', 'Scabbia: Blackmore Login')); ?></h2>
            </div>

            <div class="block_content">
                <form method="POST" action="<?php echo Http::url('blackmore/' . Blackmore::LOGIN_MODULE_INDEX); ?>">
                    <fieldset>
                        <div class="indent">
                            <?php if(isset($error)) { ?>
                            <div class="alert alert-error">
                                <?php echo $error; ?>
                            </div>
                            <?php } ?>

                            <label for="username"><?php echo _('Username:'); ?></label>
                            <div class="input-prepend">
                                <span class="add-on"><i class="icon-user"></i></span>
                                <input id="username" type="text" class="text" name="username" placeholder="Enter username" />
                            </div>

                            <label for="password"><?php echo _('Password:'); ?></label>
                            <div class="input-prepend">
                                <span class="add-on"><i class="icon-asterisk"></i></span>
                                <input id="password" type="password" class="text" name="password" placeholder="Enter password" />
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="pull-right">
                                <input type="submit" class="btn btn-primary" value="<?php echo _('Login'); ?>" />
                            </div>
                        </div>
                    </fieldset>
                </form>

            </div>
        </div>

        <script type="text/javascript">
            $l.contentEnd();
        </script>
    </body>
</html>