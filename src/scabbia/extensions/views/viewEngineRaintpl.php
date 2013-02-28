<?php

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;

/**
 * ViewEngine: RainTpl Extension
 *
 * @package Scabbia
 * @subpackage viewEngineRaintpl
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends mvc
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
 */
class ViewEngineRaintpl
{
    /**
     * @ignore
     */
    public static $engine = null;

    
    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        Views::registerViewEngine('rain', 'viewEngineRaintpl');
    }

    /**
     * @ignore
     */
    public static function renderview($uObject)
    {
        if (is_null(self::$engine)) {
            $tPath = Framework::translatePath(Config::get('raintpl/path', '{core}include/3rdparty/raintpl/inc'));
            require $tPath . '/rain.tpl.class.php';

            raintpl::configure('base_url', null);
            raintpl::configure('tpl_dir', $uObject['templatePath']);
            raintpl::configure('tpl_ext', '.rain');
            raintpl::configure('cache_dir', Framework::writablePath('cache/raintpl/'));

            if (Framework::$development >= 1) {
                raintpl::configure('check_template_update', true);
            }

            self::$engine = new \RainTPL();
        } else {
            self::$engine = new \RainTPL();
        }

        self::$engine->assign('model', $uObject['model']);
        if (is_array($uObject['model'])) {
            foreach ($uObject['model'] as $tKey => $tValue) {
                self::$engine->assign($tKey, $tValue);
            }
        }

        if (isset($uObject['extra'])) {
            foreach ($uObject['extra'] as $tKey => $tValue) {
                self::$engine->assign($tKey, $tValue);
            }
        }

        self::$engine->draw($uObject['templateFile']);
    }
}
