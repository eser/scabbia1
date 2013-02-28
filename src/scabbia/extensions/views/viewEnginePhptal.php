<?php

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Framework;

/**
 * ViewEngine: PHPTAL Extension
 *
 * @package Scabbia
 * @subpackage viewEnginePhptal
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends mvc
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
 */
class ViewEnginePhptal
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
        Views::registerViewEngine('zpt', 'viewEnginePhptal');
    }

    /**
     * @ignore
     */
    public static function renderview($uObject)
    {
        if (is_null(self::$engine)) {
            $tPath = Framework::translatePath(Config::get('phptal/path', '{core}include/3rdparty/PHPTAL'));
            require $tPath . '/PHPTAL.php';

            self::$engine = new \PHPTAL();
        } else {
            unset(self::$engine);

            // I just don't want to do it in this way,
            // but phptal.org documentation says it so.
            self::$engine = new \PHPTAL();
        }

        self::$engine->set('model', $uObject['model']);
        if (is_array($uObject['model'])) {
            foreach ($uObject['model'] as $tKey => $tValue) {
                self::$engine->set($tKey, $tValue);
            }
        }

        if (isset($uObject['extra'])) {
            foreach ($uObject['extra'] as $tKey => $tValue) {
                self::$engine->set($tKey, $tValue);
            }
        }

        self::$engine->setForceReparse(false);
        self::$engine->setTemplateRepository($uObject['templatePath']);
        self::$engine->setPhpCodeDestination(Framework::writablePath('cache/phptal/'));
        self::$engine->setOutputMode(PHPTAL::HTML5);
        self::$engine->setEncoding('UTF-8');
        self::$engine->setTemplate($uObject['templateFile']);
        if (Framework::$development >= 1) {
            self::$engine->prepare();
        }

        self::$engine->echoExecute();
    }
}
