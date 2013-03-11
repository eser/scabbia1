<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Extensions;
use Scabbia\Utils;

/**
 * Methods for essential framework functionality.
 *
 * @package Scabbia
 * @version 1.1.0
 */
class Framework
{
    const VERSION = '1.1';

    /**
     * Composer's class loader
     */
    public static $classLoader = null;
    /**
     * Indicates framework is running in production, development or debug mode
     */
    public static $development = 0;
    /**
     * Indicates framework is running in readonly mode or not
     */
    public static $readonly = false;
    /**
     * The timestamp indicates when the request started
     */
    public static $timestamp = null;
    /**
     * Indicates the base directory which framework runs in
     */
    public static $basepath = null;
    /**
     * Indicates the core directory which framework runs in
     */
    public static $corepath = null;
    /**
     * Stores relative path of running application
     */
    public static $apppath = null;
    /**
     * Stores relative path of framework root
     */
    public static $siteroot = null;
    /**
     * The milestones passed in code
     */
    public static $milestones = array();
    /**
     * Stores all available endpoints
     */
    public static $endpoints = array();
    /**
     * Stores active endpoint information
     */
    public static $endpoint = null;
    /**
     * The exit status
     */
    public static $exitStatus = null;


    /**
     * Initializes the framework.
     *
     * @param object $uClassLoader composer's class loader
     *
     * @throws \Exception
     */
    public static function load($uClassLoader = null)
    {
        self::$timestamp = microtime(true);
        self::$milestones[] = array('begin', self::$timestamp);

        self::$classLoader = $uClassLoader;

        if (is_null(self::$basepath)) {
            self::$basepath = strtr(pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_DIRNAME), DIRECTORY_SEPARATOR, '/') . '/';
        }
        self::$corepath = strtr(realpath(__DIR__ . '/../../'), DIRECTORY_SEPARATOR, '/') . '/';

        // Set error reporting occasions
        error_reporting(defined('E_STRICT') ? E_ALL | E_STRICT : E_ALL);
        // ini_set('display_errors', '1');
        // ini_set('log_errors', '0');
        // ini_set('error_log', self::$basepath . 'error.log');

        // Include framework dependencies and load them
        require self::$corepath . 'src/Patches.php';
        // require self::$corepath . 'src/Scabbia/Framework.php';
        require self::$corepath . 'src/Scabbia/Utils.php';
        require self::$corepath . 'src/Scabbia/Config.php';
        require self::$corepath . 'src/Scabbia/Events.php';
        require self::$corepath . 'src/Scabbia/Extensions.php';

        // endpoints
        if (count(self::$endpoints) > 0) {
            foreach (self::$endpoints as $tEndpoint) {
                $tParsed = parse_url($tEndpoint);
                if (!isset($tParsed['port'])) {
                    $tParsed['port'] = ($tParsed['scheme'] == 'https') ? 443 : 80;
                }

                if ($_SERVER['SERVER_NAME'] == $tParsed['host'] && $_SERVER['SERVER_PORT'] == $tParsed['port']) {
                    self::$endpoint = $tEndpoint;
                    // self::$issecure = ($tParsed['scheme'] == 'https');
                    break;
                }
            }

            if (is_null(self::$endpoint)) {
                throw new \Exception('no endpoints match.');
            }
        }

        self::$milestones[] = array('endpoints', microtime(true));

        if (!self::$readonly && is_null(self::$apppath)) {
            self::$apppath = self::$basepath . 'application/';
        }

        // load config
        Config::$default = Config::load();
        self::$milestones[] = array('configLoad', microtime(true));

        // download files
        foreach (Config::get('downloadList', array()) as $tUrl) {
            Utils::downloadFile($tUrl['filename'], $tUrl['url']);
        }
        self::$milestones[] = array('downloads', microtime(true));

        // load extensions
        Extensions::load();
        self::$milestones[] = array('extensions', microtime(true));

        // siteroot
        if (is_null(self::$siteroot)) {
            self::$siteroot = Config::get('options/siteroot', pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_DIRNAME));
        }
        self::$milestones[] = array('siteRoot', microtime(true));

        // include files
        foreach (Config::get('includeList', array()) as $tInclude) {
            $tIncludePath = pathinfo(Utils::translatePath($tInclude));

            $tFiles = Utils::glob($tIncludePath['dirname'] . '/', $tIncludePath['basename'], Utils::GLOB_FILES);
            if ($tFiles !== false) {
                foreach ($tFiles as $tFilename) {
                    //! todo require_once?
                    include $tFilename;
                }
            }
        }
        self::$milestones[] = array('includesLoad', microtime(true));

        // output handling
        ob_start('Scabbia\\Framework::output');
        ob_implicit_flush(false);
    }

    /**
     * Calls
     *
     *
     */
    public static function run($uCallbacks = null, $uOtherwise = null)
    {
        // run extensions
        $tParms = array();
        Events::invoke('run', $tParms);
        self::$milestones[] = array('extensionsRun', microtime(true));

        if(!is_null($uCallbacks)) {
            foreach ((array)$uCallbacks as $tCallback) {
                $tReturn = call_user_func($tCallback);

                if (!is_null($tReturn) && $tReturn === true) {
                    break;
                }
            }

            if (!is_null($uOtherwise) && !isset($tReturn) || $tReturn !== true) {
                call_user_func($uOtherwise);
                return false;
            }
        }

        return true;
    }

    /**
     * Output callback method which will be called when the output buffer
     * is flushed at the end of the request.
     *
     * @param string $uValue the generated content
     * @param int $uStatus the status of the output buffer
     *
     * @return string final content
     */
    public static function output($uValue, $uStatus)
    {
        $tParms = array(
            'exitStatus' => &self::$exitStatus,
            'content' => &$uValue
        );

        Events::invoke('output', $tParms);

        //! check invoke order
        if (ini_get('output_handler') == '') {
            $tParms['content'] = mb_output_handler($tParms['content'], $uStatus); // PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_END

            if (!ini_get('zlib.output_compression') && (PHP_SAPI != 'cli') && Config::get('options/gzip', '1') != '0') {
                $tParms['content'] = ob_gzhandler($tParms['content'], $uStatus); // PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_END
            }
        }

        return $tParms['content'];
    }

    /**
     * Terminates the execution of the framework.
     *
     * @param int $uLevel the exit status (0-254)
     * @param string $uErrorMessage the error message if available
     */
    public static function end($uLevel = 0, $uErrorMessage = null)
    {
        self::$exitStatus = array($uLevel, $uErrorMessage);
        ob_end_flush();

        exit($uLevel);
    }
}
