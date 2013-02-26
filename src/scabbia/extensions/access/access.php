<?php

namespace Scabbia\Extensions\Access;

use Scabbia\Extensions\Views\Views;
use Scabbia\Config;
use Scabbia\Extensions;
use Scabbia\Framework;

/**
 * Access Extension
 *
 * @package Scabbia
 * @subpackage access
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
 */
class Access
{
    /**
     * @ignore
     */
    public static $maintenance = false;
    /**
     * @ignore
     */
    public static $maintenanceExcludeIps = array();
    /**
     * @ignore
     */
    public static $ipFilters = array();


    /**
     * @ignore
     */
    public static function run()
    {
        self::$maintenance = (intval(Config::get('/access/maintenance/mode', '0')) >= 1);
        self::$maintenanceExcludeIps = Config::get('/access/maintenance/ipExcludeList', array());

        foreach (Config::get('/access/ipFilter/ipFilterList', array()) as $tIpFilterList) {
            if (preg_match('/^' . str_replace(array('.', '*', '?'), array('\\.', '[0-9]{1,3}', '[0-9]{1}'), $tIpFilterList['pattern']) . '$/i', $_SERVER['REMOTE_ADDR'])) {
                if ($tIpFilterList['type'] == 'allow') {
                    self::$ipFilters = array();
                    continue;
                }

                self::$ipFilters[] = $tIpFilterList['pattern'];
            }
        }

        if (self::$maintenance && !in_array($_SERVER['REMOTE_ADDR'], self::$maintenanceExcludeIps)) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable', true, 503);
            header('Retry-After: 600', true);

            $tMvcPage = Config::get('/access/maintenance/mvcpage', null);
            if (!is_null($tMvcPage)) {
                Views::view($tMvcPage);
            } else {
                $tFile = Framework::translatePath(Config::get('/access/maintenance/page'));
                include $tFile;
            }

            // to interrupt event-chain execution
            return false;
        }

        if (count(self::$ipFilters) > 0) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);

            $tMvcPage = Config::get('/access/ipFilter/mvcpage', null);
            if (!is_null($tMvcPage)) {
                Views::view($tMvcPage);
            } else {
                $tFile = Framework::translatePath(Config::get('/access/ipFilter/page'));
                include $tFile;
            }

            // to interrupt event-chain execution
            return false;
        }

        return null;
    }
}
