<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\I18n\I18n;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Io;
use Scabbia\Utils;

/**
 * Views Extension
 *
 * @package Scabbia
 * @subpackage Views
 * @version 1.1.0
 *
 * @todo Views::csv()
 */
class Views
{
    /**
     * @ignore
     */
    public static $viewEngines = null;


    /**
     * @ignore
     */
    public static function viewFile($uView, $uModel = null)
    {
        $tViewFilePath = Io::translatePath($uView);
        $tViewFileInfo = pathinfo($tViewFilePath);

        if (self::$viewEngines === null) {
            self::$viewEngines = Config::get('views/viewEngineList', array());
        }

        if (!isset(self::$viewEngines[$tViewFileInfo['extension']])) {
            $tViewFileInfo['extension'] = Config::get('views/defaultViewExtension', 'php');
        }

        $tTemplatePath = $tViewFileInfo['dirname'] . '/';
        $tViewFile = $tViewFileInfo['basename'];

        $tViewArray = array(
            'templatePath' => &$tTemplatePath,
            'templateFile' => &$tViewFile,
            'compiledFile' => hash('adler32', $uView) . '-' . $tViewFileInfo['basename'],
            'model' => &$uModel
        );

        call_user_func(
            self::$viewEngines[$tViewFileInfo['extension']] . '::renderview',
            $tViewArray
        );
    }

    /**
     * @ignore
     */
    public static function json($uModel)
    {
        echo json_encode($uModel);
    }

    /**
     * @ignore
     */
    public static function xml($uModel)
    {
        echo '<?xml version="1.0" encoding="UTF-8" ?>';
        echo '<xml>';
        self::xmlRecursive($uModel);
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
                    echo '<item index="', $tKey, '">';
                    $tKey = 'item';
                } else {
                    echo '<', $tKey, '>';
                }

                self::xmlRecursive($tValue);
                echo '</', $tKey, '>';
            }

            return;
        }

        echo htmlspecialchars($uObject, ENT_NOQUOTES, mb_internal_encoding());
    }
}
