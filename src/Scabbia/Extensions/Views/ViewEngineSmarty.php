<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Io;

/**
 * Views Extension: ViewEngineSmarty Class
 *
 * @package Scabbia
 * @subpackage Views
 * @version 1.1.0
 */
class ViewEngineSmarty
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
        Views::registerViewEngine('tpl', 'Scabbia\\Extensions\\Views\\ViewEngineSmarty');
    }

    /**
     * @ignore
     */
    public static function renderview($uObject)
    {
        if (is_null(self::$engine)) {
            $tPath = Io::translatePath(Config::get('smarty/path', '{core}include/3rdparty/smarty/libs'));
            require $tPath . '/Smarty.class.php';

            self::$engine = new \Smarty();

            self::$engine->setTemplateDir($uObject['templatePath']);
            self::$engine->setCompileDir(Io::translatePath('{writable}cache/smarty/'));

            if (Framework::$disableCaches) {
                self::$engine->force_compile = true;
            }
        } else {
            self::$engine->clearAllAssign();
        }

        self::$engine->assignByRef('model', $uObject['model']);
        if (is_array($uObject['model'])) {
            foreach ($uObject['model'] as $tKey => $tValue) {
                self::$engine->assignByRef($tKey, $tValue);
            }
        }

        if (isset($uObject['extra'])) {
            foreach ($uObject['extra'] as $tKey => $tValue) {
                self::$engine->assignByRef($tKey, $tValue);
            }
        }

        self::$engine->display($uObject['templateFile']);
    }
}
