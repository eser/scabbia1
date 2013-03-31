<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Extensions;

/**
 * Datasources Extension
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
class Datasources
{
    /**
     * @ignore
     */
    public static $datasources = null;
    /**
     * @ignore
     */
    public static $types = array();


    /**
     * @ignore
     */
    public static function get($uDatasource = null)
    {
        if (is_null(self::$datasources)) {
            $tParms = array();
            Events::invoke('registerDatasources', $tParms);

            foreach (Extensions::getSubclasses('Scabbia\\Extensions\\Datasources\\IDatasource', true) as $tClass) {
                self::$types[$tClass::$type] = $tClass;
            }

            foreach (Config::get('datasourceList', array()) as $tDatasourceConfig) {
                $tDatasource = new self::$types[$tDatasourceConfig['type']] ($tDatasourceConfig);
                self::$datasources[$tDatasourceConfig['id']] = $tDatasource;
            }
        }

        // default name is dbconn
        if (is_null($uDatasource)) {
            $uDatasource = 'dbconn';
        }

        return self::$datasources[$uDatasource];
    }
}
