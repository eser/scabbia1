<?php
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Extensions\Panel\Controllers\Panel;
use Scabbia\Extensions\Helpers\Html;
use Scabbia\Extensions\Http\Http;
use Scabbia\Extensions\Views\Views;

?>
<?php echo Html::doctype('html5'); ?>
<html lang="en-us">
    <head>
        <meta charset="utf-8" />

        <title><?php echo _(Config::get('panel/title', 'Scabbia: Panel')); ?></title>
        <link rel="shortcut icon" href="<?php echo Http::url('scabbia/favicon.ico'); ?>" type="image/x-icon" />

        <link type="text/css" href="<?php echo Http::url('scabbia.css?core,jquery,validation,bootstrap,cleditor,tablesorter,panel'); ?>" rel="stylesheet" media="all" />
        <script type="text/javascript" src="<?php echo Http::url('scabbia.js?core,jquery,validation,bootstrap,cleditor,tablesorter,flot,panel'); ?>"></script>
    </head>
    <body class="<?php echo Config::get('panel/bodyStyle', 'stretch'); ?> login">
        <script type="text/javascript">
            $l.contentBegin('login', '<?php echo $root; ?>/');
        </script>

        <div class="block">

            <div class="block_head">
                <h2><?php echo _(Config::get('panel/loginTitle', 'Scabbia: Panel Login')); ?></h2>
            </div>

            <div class="block_content">
                <form method="POST" action="<?php echo Http::url('panel/' . Panel::LOGIN_MODULE_INDEX); ?>">
                    <fieldset>
                        <div class="indent">
                            <?php Views::viewFile('{core}views/panel/sectionError.php'); ?>

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