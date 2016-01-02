<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
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
        if (self::$autoModels === null) {
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
