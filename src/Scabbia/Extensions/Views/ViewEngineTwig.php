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
 * Views Extension: ViewEngineTwig Class
 *
 * @package Scabbia
 * @subpackage Views
 * @version 1.1.0
 */
class ViewEngineTwig
{
    /**
     * @ignore
     */
    public static $loader = null;
    /**
     * @ignore
     */
    public static $engine = null;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        Views::registerViewEngine('twig', 'Scabbia\\Extensions\\Views\\ViewEngineTwig');
    }

    /**
     * @ignore
     */
    public static function renderview($uObject)
    {
        if (is_null(self::$engine)) {
            $tPath = Io::translatePath(Config::get('twig/path', '{core}include/3rdparty/twig/lib/Twig'));
            require $tPath . '/Autoloader.php';

            Twig_Autoloader::register();
            self::$loader = new \Twig_Loader_Filesystem($uObject['templatePath']);

            $tOptions = array(
                'cache' => Io::translatePath('{writable}cache/twig/')
            );

            if (Framework::$development >= 1) {
                $tOptions['auto_reload'] = true;
            }

            self::$engine = new \Twig_Environment(self::$loader, $tOptions);
        }

        $model = array('model' => &$uObject['model']);

        if (is_array($uObject['model'])) {
            $model = array_merge($model, $uObject['model']);
        }

        if (isset($uObject['extra'])) {
            $model = array_merge($model, $uObject['extra']);
        }

        echo self::$engine->render($uObject['templateFile'], $model);
    }
}
