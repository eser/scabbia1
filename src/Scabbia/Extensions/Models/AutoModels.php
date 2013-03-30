<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Models;

use Scabbia\Config;

/**
 * Models Extension: AutoModels Class
 *
 * @package Scabbia
 * @subpackage Models
 * @version 1.1.0
 */
class AutoModels
{
    /**
     * @ignore
     */
    public static $autoModels = null;


    /**
     * @ignore
     */
    public static function load()
    {
        if (is_null(self::$autoModels)) {
            self::$autoModels = array();

            foreach (Config::get('autoModelList', array()) as $tAutoModelKey => $tAutoModel) {
                self::$autoModels[$tAutoModelKey] = $tAutoModel;
            }
        }
    }

    /**
     * @ignore
     */
    public static function get($uEntityName)
    {
        self::load();

        return self::$autoModels[$uEntityName];
    }
}
