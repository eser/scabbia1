<?php

namespace Scabbia\Extensions\Datasources;

use Scabbia\Config;

/**
 * Datasources Extension
 *
 * @package Scabbia
 * @subpackage datasources
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
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
