<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Http;

use Scabbia\Extensions\Http\Http;
use Scabbia\Config;
use Scabbia\Extensions;
use Scabbia\Utils;

/**
 * Http Extension: Router Class
 *
 * @package Scabbia
 * @subpackage Http
 * @version 1.1.0
 */
class Router
{
    /**
     * @ignore
     */
    public static $rewrites = null;
    /**
     * @ignore
     */
    public static $routes = null;


    /**
     * @ignore
     */
    private static function load()
    {
        if (!is_null(self::$rewrites)) {
            return;
        }

        self::$rewrites = new \SplPriorityQueue();
        foreach (Config::get('http/rewriteList', array()) as $tRewriteList) {
            self::addRewrite(
                $tRewriteList['match'],
                $tRewriteList['forward'],
                isset($tRewriteList['priority']) ? (int)$tRewriteList['priority'] : 10
            );
        }

        self::$routes = new \SplPriorityQueue();
        foreach (Config::get('http/routeList', array()) as $tRouteList) {
            $tDefaults = array();
            foreach ($tRouteList as $tRouteListKey => $tRouteListItem) {
                if (strncmp($tRouteListKey, 'defaults/', 9) == 0) {
                    $tDefaults[substr($tRouteListKey, 9)] = $tRouteListItem;
                }
            }

            self::addRoute(
                $tRouteList['match'],
                $tRouteList['callback'],
                $tDefaults,
                isset($tRewriteList['priority']) ? (int)$tRewriteList['priority'] : 10
            );
        }
    }

    /**
     * @ignore
     */
    public static function addRewrite($uMatch, $uForward, $uPriority = 10)
    {
        self::load();

        foreach ((array)$uMatch as $tMatch) {
            $tParts = explode(' ', $tMatch, 2);
            $tLimitMethods = ((count($tParts) > 1) ? explode(',', strtolower(array_shift($tParts))) : null);

            self::$rewrites->insert(array($tParts[0], $uForward, $tLimitMethods), $uPriority);
        }
    }

    /**
     * @ignore
     */
    public static function addRoute($uMatch, $uCallback, array $uDefaults = array(), $uPriority = 10)
    {
        self::load();

        foreach ((array)$uMatch as $tMatch) {
            $tParts = explode(' ', $tMatch, 2);
            $tLimitMethods = ((count($tParts) > 1) ? explode(',', strtolower(array_shift($tParts))) : null);

            self::$routes->insert(array($tParts[0], $uCallback, $tLimitMethods, $uDefaults), $uPriority);
        }
    }

    /**
     * @ignore
     */
    public static function resolve($uQueryString, $uMethod = null, $uMethodExt = null)
    {
        self::load();

        $tMethod = strtolower($uMethod);

        // @todo use self::$routes->top() if needed.
        foreach (self::$rewrites as $tRewriteItem) {
            if (isset($tRewriteItem[2]) && !is_null($uMethod) && !in_array($tMethod, $tRewriteItem[2])) {
                continue;
            }

            if (Http::rewriteUrl($uQueryString, $tRewriteItem['match'], $tRewriteItem['forward'])) {
                break;
            }
        }

        // @todo use self::$routes->top() if needed.
        foreach (self::$routes as $tRouteItem) {
            if (isset($tRouteItem[2]) && !is_null($uMethod) && !in_array($uMethod, $tRouteItem[2])) {
                continue;
            }

            $tMatches = Utils::pregMatch(ltrim($tRouteItem[0], '/'), $uQueryString);

            if (count($tMatches) > 0) {
                $tParameters = array(
                    'method' => $uMethod,
                    'methodext' => $uMethodExt
                );

                foreach ($tRouteItem[3] as $tDefaultKey => $tDefaultItem) {
                    if (isset($tMatches[$tDefaultKey])) {
                        $tParameters[$tDefaultKey] = $tMatches[$tDefaultKey];
                    } else {
                        $tParameters[$tDefaultKey] = $tDefaultItem;
                    }
                }

                return array($uQueryString, $tRouteItem[1], $tParameters);
            }
        }

        return array($uQueryString, null, null);
    }
}
