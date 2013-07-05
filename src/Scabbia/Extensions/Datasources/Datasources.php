<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
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
    public static $interfaces = null;


    /**
     * @ignore
     */
    public static function get($uDatasource = null)
    {
        if (self::$interfaces === null) {
            self::$interfaces = Config::get('dataInterfaceList', array());
        }

        if (self::$datasources === null) {
            foreach (Config::get('datasourceList', array()) as $tDatasourceConfig) {
                $tDatasource = new self::$interfaces[$tDatasourceConfig['interface']] ($tDatasourceConfig);
                self::$datasources[$tDatasourceConfig['id']] = $tDatasource;
            }
        }

        // default name is dbconn
        if ($uDatasource === null) {
            $uDatasource = 'dbconn';
        }

        return self::$datasources[$uDatasource];
    }

    /**
     * @ignore
     */
    public static function add($uId, $uInterface, array $uConfig = array())
    {
        $uConfig['id'] = $uId;
        $uConfig['interface'] = $uInterface;

        self::$datasources[$uId] = new self::$interfaces[$uInterface] ($uConfig);
    }
}
