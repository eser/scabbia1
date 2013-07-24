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
 * Views Extension: ViewEnginePhptal Class
 *
 * @package Scabbia
 * @subpackage Views
 * @version 1.1.0
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
    public static function renderview($uObject)
    {
        if (self::$engine === null) {
            $tPath = Io::translatePath(Config::get('phptal/path', '{core}include/3rdparty/PHPTAL'));
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

        foreach (Framework::$variables as $tKey => $tValue) {
            self::$engine->set($tKey, $tValue);
        }

        self::$engine->setForceReparse(false);
        self::$engine->setTemplateRepository($uObject['templatePath']);
        self::$engine->setPhpCodeDestination(Io::translatePath('{writable}cache/phptal/'));
        self::$engine->setOutputMode(PHPTAL::HTML5);
        self::$engine->setEncoding('UTF-8');
        self::$engine->setTemplate($uObject['templateFile']);

        if (Framework::$disableCaches) {
            self::$engine->prepare();
        }

        self::$engine->echoExecute();
    }
}
