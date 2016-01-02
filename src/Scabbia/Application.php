<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia;

use Scabbia\Delegate;

/**
 * Default application.
 *
 * @package Scabbia
 * @version 1.1.0
 *
 * @todo generate('GET', 'test/test');
 */
class Application
{
    /**
     * @var string      application name
     */
    public $name;
    /**
     * @var string      application path
     */
    public $path;
    /**
     * @var array       callback definitions
     */
    public $callbacks;
    /**
     * @var null|string if any of callbacks does not fit
     */
    public $otherwise = null;
    /**
     * @var null|string if any error occurs during process
     */
    public $onError = null;
    /**
     * @var array       before delegate
     */
    public $before;
    /**
     * @var array       after delegate
     */
    public $after;
    /**
     * @ignore
     */
    public $rewrites = null;
    /**
     * @ignore
     */
    public $routes = null;


    /**
     * Default entry point and definitions for an application.
     *
     * @param string $uName      application name
     * @param string $uDirectory application directory
     */
    public function __construct($uName = null, $uDirectory = null)
    {
        $this->name = ($uName !== null) ? $uName : 'Application';
        $this->path = Framework::$basepath . (($uDirectory !== null) ? $uDirectory : 'application/');

        $this->before = new Delegate();
        $this->after = new Delegate();

        $this->callbacks = new Delegate(true);
        $this->callbacks->add('Scabbia\\Extensions\\Http\\Http::routing');
        $this->callbacks->add('Scabbia\\Extensions\\Assets\\Assets::routing');

        $this->otherwise = 'Scabbia\\Extensions\\Http\\Http::notfound';
        $this->onError = 'Scabbia\\Extensions\\Http\\Http::error';
    }

    /**
     * @ignore
     */
    private function load()
    {
        if ($this->rewrites !== null) {
            return;
        }

        $this->rewrites = new \SplPriorityQueue();
        foreach (Config::get('http/rewriteList', array()) as $tRewriteList) {
            $this->addRewrite(
                $tRewriteList['match'],
                $tRewriteList['forward'],
                isset($tRewriteList['priority']) ? (int)$tRewriteList['priority'] : 10
            );
        }

        $this->routes = new \SplPriorityQueue();
        foreach (Config::get('http/routeList', array()) as $tRouteList) {
            $tDefaults = array();
            foreach ($tRouteList as $tRouteListKey => $tRouteListItem) {
                if (strncmp($tRouteListKey, 'defaults/', 9) === 0) {
                    $tDefaults[substr($tRouteListKey, 9)] = $tRouteListItem;
                }
            }

            $this->addRoute(
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
    public function rewriteUrl(&$uUrl, $uMatch, $uForward)
    {
        $tReturn = Utils::pregReplace($uMatch, $uForward, $uUrl);
        if ($tReturn !== false) {
            $uUrl = $tReturn;

            return true;
        }

        return false;
    }

    /**
     * @ignore
     */
    public function addRewrite($uMatch, $uForward, $uPriority = 10)
    {
        $this->load();

        foreach ((array)$uMatch as $tMatch) {
            $tParts = explode(' ', $tMatch, 2);
            $tLimitMethods = ((count($tParts) > 1) ? explode(',', strtolower(array_shift($tParts))) : null);

            $this->rewrites->insert(array($tParts[0], $uForward, $tLimitMethods), $uPriority);
        }
    }

    /**
     * @ignore
     */
    public function addRoute($uMatch, /* callable */ $uCallback, array $uDefaults = array(), $uPriority = 10)
    {
        $this->load();

        foreach ((array)$uMatch as $tMatch) {
            $tParts = explode(' ', $tMatch, 2);
            $tLimitMethods = ((count($tParts) > 1) ? explode(',', strtolower(array_shift($tParts))) : null);

            $this->routes->insert(array($tParts[0], $uCallback, $tLimitMethods, $uDefaults), $uPriority);
        }
    }

    /**
     * @ignore
     */
    public function resolve($uPathInfo, $uMethod = null, $uMethodExt = null)
    {
        $this->load();

        // @todo use $this->routes->top() if needed.
        foreach ($this->rewrites as $tRewriteItem) {
            if (isset($tRewriteItem[2]) && $uMethod !== null && !in_array($uMethod, $tRewriteItem[2])) {
                continue;
            }

            if ($this->rewriteUrl($uPathInfo, $tRewriteItem['match'], $tRewriteItem['forward'])) {
                break;
            }
        }

        // @todo use $this->routes->top() if needed.
        foreach ($this->routes as $tRouteItem) {
            if (isset($tRouteItem[2]) && $uMethod !== null && !in_array($uMethod, $tRouteItem[2])) {
                continue;
            }

            $tMatches = Utils::pregMatch(ltrim($tRouteItem[0], '/'), $uPathInfo);

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

                return array($uPathInfo, $tRouteItem[1], $tParameters);
            }
        }

        return array($uPathInfo, null, null);
    }
}
