<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Config;
use Scabbia\Events;

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
    public static $default = null;


    /**
     * @ignore
     */
    public static function get($uDatasource = null)
    {
        if (is_null(self::$datasources)) {
            self::$datasources = array();

            $tParms = array(
                'datasources' => &self::$datasources
            );
            Events::invoke('datasourcesRegister', $tParms);

            foreach (Config::get('datasourceList', array()) as $tDatasourceConfig) {
                $tDatasource = new self::$types[$tDatasourceConfig['type']]['datasource'] ($tDatasourceConfig);
                self::$datasources[$tDatasourceConfig['id']] = $tDatasource;

                if (is_null(self::$default) || $tDatasource->default) {
                    self::$default = self::$datasources[$tDatasourceConfig['id']];
                }
            }
        }

        if (is_null($uDatasource)) {
            return self::$default;
        }

        return self::$datasources[$uDatasource];
    }

    /**
     * @ignore
     */
    public static function registerType($uName, $uDatasourceClass, $uDataProviderClass)
    {
        if (isset(self::$types[$uName])) {
            return;
        }

        self::$types[$uName] = array(
            'datasource' => $uDatasourceClass,
            'provider' => $uDataProviderClass
        );
    }
}
