<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Io;

/**
 * Views Extension: ViewEngineRaintpl Class
 *
 * @package Scabbia
 * @subpackage Views
 * @version 1.1.0
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
        Views::registerViewEngine('rain', 'Scabbia\\Extensions\\Views\\ViewEngineRaintpl');
    }

    /**
     * @ignore
     */
    public static function renderview($uObject)
    {
        if (is_null(self::$engine)) {
            $tPath = Io::translatePath(Config::get('raintpl/path', '{core}include/3rdparty/raintpl/inc'));
            require $tPath . '/rain.tpl.class.php';

            raintpl::configure('base_url', null);
            raintpl::configure('tpl_dir', $uObject['templatePath']);
            raintpl::configure('tpl_ext', '.rain');
            raintpl::configure('cache_dir', Io::translatePath('{writable}cache/raintpl/'));

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
