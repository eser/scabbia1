<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\I18n\I18n;
use Scabbia\Extensions\Mvc\Mvc;
use Scabbia\Config;
use Scabbia\Extensions;
use Scabbia\Framework;
use Scabbia\Io;

/**
 * Views Extension
 *
 * @package Scabbia
 * @subpackage Views
 * @version 1.1.0
 *
 * @todo register viewengines by getSubclasses
 */
class Views
{
    /**
     * @ignore
     */
    public static $viewEngines = array();
    /**
     * @ignore
     */
    public static $vars = array();


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        foreach (Config::get('mvc/view/viewEngineList', array()) as $tViewEngine) {
            self::registerViewEngine($tViewEngine['extension'], $tViewEngine['class']);
        }

        self::registerViewEngine('php', 'Scabbia\\Extensions\\Views\\ViewEnginePhp');
    }

    /**
     * @ignore
     */
    public static function registerViewEngine($uExtension, $uClassName)
    {
        if (isset(self::$viewEngines[$uExtension])) {
            return;
        }

        self::$viewEngines[$uExtension] = $uClassName;
    }

    /**
     * @ignore
     */
    public static function get($uKey)
    {
        return self::$vars[$uKey];
    }

    /**
     * @ignore
     */
    public static function set($uKey, $uValue)
    {
        self::$vars[$uKey] = $uValue;
    }

    /**
     * @ignore
     */
    public static function setRef($uKey, &$uValue)
    {
        self::$vars[$uKey] = $uValue;
    }

    /**
     * @ignore
     */
    public static function setRange(array $uArray)
    {
        foreach ($uArray as $tKey => $tValue) {
            self::$vars[$tKey] = $tValue;
        }
    }

    /**
     * @ignore
     */
    public static function remove($uKey)
    {
        unset(self::$vars[$uKey]);
    }

    /**
     * @ignore
     */
    public static function viewFile($uView, $uModel = null)
    {
        if (is_null($uModel)) {
            $uModel = & self::$vars;
        }

        $tViewFilePath = Io::translatePath($uView);
        $tViewFileInfo = pathinfo($tViewFilePath);
        if (!isset(self::$viewEngines[$tViewFileInfo['extension']])) {
            $tViewFileInfo['extension'] = Config::get('mvc/view/defaultViewExtension', 'php');
        }

        $tExtra = array(
            'root' => Framework::$siteroot
        );

        $tExtra['lang'] = I18n::$language['key'];
        $tExtra['controller'] = Mvc::current();

        $tTemplatePath = pathinfo($tViewFilePath, PATHINFO_DIRNAME) . '/';
        $tViewFile = pathinfo($tViewFilePath, PATHINFO_BASENAME);

        $tViewArray = array(
            'templatePath' => &$tTemplatePath,
            'templateFile' => &$tViewFile,
            'compiledFile' => hash('adler32', $uView) . '-' . $tViewFileInfo['basename'],
            'model' => &$uModel,
            'extra' => &$tExtra
        );

        call_user_func(
            self::$viewEngines[$tViewFileInfo['extension']] . '::renderview',
            $tViewArray
        );
    }

    /**
     * @ignore
     */
    public static function json($uModel = null)
    {
        echo json_encode(
            (!is_null($uModel) ? $uModel : self::$vars)
        );
    }

    /**
     * @ignore
     */
    public static function xml($uModel = null)
    {
        echo '<?xml version="1.0" encoding="UTF-8" ?>';
        echo '<xml>';
        self::xmlRecursive((!is_null($uModel) ? $uModel : self::$vars));
        echo '</xml>';
    }

    /**
     * @ignore
     */
    private static function xmlRecursive($uObject)
    {
        if (is_array($uObject) || is_object($uObject)) {
            foreach ($uObject as $tKey => $tValue) {
                if (is_numeric($tKey)) {
                    echo '<item index="' . $tKey . '">';
                    $tKey = 'item';
                } else {
                    echo '<' . $tKey . '>';
                }

                self::xmlRecursive($tValue);
                echo '</' . $tKey . '>';
            }

            return;
        }

        echo htmlspecialchars($uObject, ENT_NOQUOTES);
    }
}
