<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Zmodels;

use Scabbia\Extensions\Zmodels\Zmodel;
use Scabbia\Config;

/**
 * Zmodels Extension
 *
 * @package Scabbia
 * @subpackage Zmodels
 * @version 1.1.0
 */
class Zmodels
{
    /**
     * @ignore
     */
    public static $zmodels = null;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$zmodels = array();

        foreach (Config::get('zmodelList', array()) as $tZmodel) {
            self::$zmodels[$tZmodel['name']] = $tZmodel;
        }
    }

    /**
     * @ignore
     */
    public static function get($uEntityName)
    {
        return new Zmodel($uEntityName);
    }
}
