<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Config;
use Scabbia\Events;
use Scabbia\Io;

/**
 * Methods for essential framework functionality.
 *
 * @package Scabbia
 * @version 1.1.0
 *
 * @todo determine application before apppath, get apppath from application's itself
 * @todo completely independent architecture allows no-application, maybe same with $readonly?
 * @todo Request abstract classes attached to Framework (will be derived CliRequest, HttpRequest etc.)
 * @todo Response abstract classes attached to Framework (will be derived HttpResponse, CliResponse etc.)
 * @todo HttpResponse might have OutputAdapter (Html, Xml, Json, PDF, DownloadFile-Binary etc.) and OutputEncoding
 * @todo binder extension, the same functionality with the assets also includes compilation of files (with list on config)
 */
class Framework
{
    /**
     * @var string  Scabbia Framework's version
     */
    const VERSION = '1.1';

    /**
     * @var object  Composer's class loader
     */
    public static $classLoader = null;
    /**
     * @var object  Array of loaded applications
     */
    public static $applications = array();
    /**
     * @var object  Application instance
     */
    public static $application = null;
    /**
     * @var int     Indicates framework is running in production, development or debug mode
     */
    public static $development = false;
    /**
     * @var bool    Indicates caching is disabled or not
     */
    public static $disableCaches = false;
    /**
     * @var bool    Indicates framework is running in readonly mode or not
     */
    public static $readonly = false;
    /**
     * @var int     The timestamp indicates when the request started
     */
    public static $timestamp = null;
    /**
     * @var string  Indicates the base directory which framework runs in
     */
    public static $basepath = null;
    /**
     * @var string  Indicates the core directory which framework runs in
     */
    public static $corepath = null;
    /**
     * @var string  Indicates the vendor directory which dependencies can be found at
     */
    public static $vendorpath = null;
    /**
     * @var string  Stores relative path of running application
     *
     * @todo probably wrong place - app
     */
    public static $apppath = null;
    /**
     * @var int     The exit status
     *
     * @todo probably wrong place
     */
    public static $exitStatus = null;
    /**
     * @var string  Response format
     *
     * @todo wrong place
     */
    public static $responseFormat = 'html';


    /**
     * Initializes the framework.
     *
     * @param object|null $uClassLoader composer's class loader
     *
     * @throws \Exception
     */
    public static function load($uClassLoader = null)
    {
        // Set framework autoloader
        if (!is_null($uClassLoader)) {
            self::$classLoader = $uClassLoader;
            self::$classLoader->unregister();
        }

        spl_autoload_register('Scabbia\\Framework::loadClass');

        // Set internal encoding
        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding('UTF-8');
        }

        // Set error reporting occasions
        error_reporting(defined('E_STRICT') ? E_ALL | E_STRICT : E_ALL);

        // Set variables
        if (is_null(self::$basepath)) {
            $tPath = getcwd();
            if (isset($_SERVER['argv']) && ($tDirname = dirname($_SERVER['argv'][0])) != '.') {
                $tPath .= '/' . $tDirname;
            }

            self::$basepath = strtr($tPath, '\\', '/') . '/';
        }
        self::$corepath = strtr(realpath(__DIR__ . '/../../'), '\\', '/') . '/';
        self::$vendorpath = self::$basepath . 'vendor/';

        self::$timestamp = microtime(true);
    }

    /**
     * Custom class loader.
     *
     * @param string $uName name of the class.
     *
     * @return bool whether class is loaded or not
     */
    public static function loadClass($uName)
    {
        foreach (self::$applications as $tApplication) {
            $tLen = strlen($tApplication['namespace']);
            if (strncmp($tApplication['namespace'], $uName, $tLen) == 0) {
                $tName = Io::namespacePath(substr($uName, $tLen)) . '.php';

                // try in application directory first
                if (file_exists($tFile = $tApplication['directory'] . $tName)) {
                    //! todo require_once?
                    include $tFile;
                    return true;
                }

                /*
                // @todo applications' classLoadList itself (load app's config)
                foreach (Config::get('classPathList', array()) as $tClassLoader) {
                    if (file_exists($tFile = Io::translatePath($tClassLoader) . '/' . $tName)) {
                        //! todo require_once?
                        include $tFile;
                        return true;
                    }
                }
                */
            }
        }

        if (!is_null(self::$classLoader)) {
            return self::$classLoader->loadClass($uName);
        }

        return false;
    }

    /**
     * @ignore
     */
    public static function addApplication($uNamespace, $uDirectory = null, $uEndpoints = array())
    {
        self::$applications[] = array(
            'namespace' => $uNamespace,
            'directory' => (!is_null($uDirectory) ? rtrim($uDirectory, '/') : Io::namespacePath($uNamespace)) . '/',
            'endpoints' => (array)$uEndpoints
        );
    }

    /**
     * Invokes the startup methods just for framework extensions so other parties can take over execution.
     */
    public static function run()
    {
        // determine active application
        $tSelectedApplication = null;

        foreach (self::$applications as $tApplication) {
            if (count($tApplication['endpoints']) > 0) {
                foreach ($tApplication['endpoints'] as $tEndpoint) {
                    foreach ((array)$tEndpoint['address'] as $tEndpointAddress) {
                        $tParsed = parse_url($tEndpointAddress);

                        if (!isset($tParsed['port'])) {
                            $tParsed['port'] = ($tParsed['scheme'] == 'https') ? 443 : 80;
                        }

                        if ($_SERVER['SERVER_NAME'] == $tParsed['host'] && $_SERVER['SERVER_PORT'] == $tParsed['port']) {
                            $tSelectedApplication = $tApplication;
                            break 3;
                        }
                    }
                }
            } else {
                $tSelectedApplication = $tApplication;
                break;
            }
        }

        // construct application object
        if (!is_null($tSelectedApplication)) {
            self::$application = new Application($tSelectedApplication['namespace'], $tSelectedApplication['directory']);
            self::$apppath = self::$basepath . self::$application->directory;
        }

        // load configuration w/ extensions
        Config::$default = Config::load();

        // include files
        foreach (Config::get('includeList', array()) as $tInclude) {
            $tIncludePath = pathinfo(Io::translatePath($tInclude));

            $tFiles = Io::glob($tIncludePath['dirname'] . '/', $tIncludePath['basename'], Io::GLOB_FILES);
            if ($tFiles !== false) {
                foreach ($tFiles as $tFilename) {
                    //! todo require_once?
                    include $tFilename;
                }
            }
        }

        // loadClass classes
        foreach (Config::get('loadClassList', array()) as $tClass) {
            class_exists($tClass, true);
        }

        // events
        foreach (Config::get('eventList', array()) as $tLoad) {
            if ($tLoad['name'] == 'load') {
                Events::invokeSingle(array($tLoad['type'], $tLoad['value']));
                continue;
            }

            Events::register($tLoad['name'], $tLoad['type'], $tLoad['value']);
        }

        // output handling
        ob_start('Scabbia\\Framework::output');
        ob_implicit_flush(false);

        // ignite application
        if (!is_null($tSelectedApplication)) {
            // run extensions
            $tParms = array(
                'onerror' => self::$application->onError
            );
            Events::invoke('pre-run', $tParms);

            foreach (self::$application->callbacks as $tCallback) {
                $tReturn = call_user_func($tCallback);

                if (!is_null($tReturn) && $tReturn === true) {
                    break;
                }
            }

            if (!is_null(self::$application->otherwise) && !isset($tReturn) || $tReturn !== true) {
                call_user_func(self::$application->otherwise);
                return false;
            }
        }

        return true;
    }

    /**
     * Output callback method which will be called when the output buffer
     * is flushed at the end of the request.
     *
     * @param string    $uValue     the generated content
     * @param int       $uStatus    the status of the output buffer
     *
     * @return string final content
     */
    public static function output($uValue, $uStatus)
    {
        $tParms = array(
            'exitStatus' => &self::$exitStatus,
            'responseFormat' => &self::$responseFormat,
            'content' => &$uValue
        );

        Events::invoke('output', $tParms);

        if (ini_get('output_handler') == '') {
            $tParms['content'] = mb_output_handler(
                $tParms['content'],
                $uStatus
            ); // PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_END

            if (!ini_get('zlib.output_compression') &&
                (PHP_SAPI != 'cli') &&
                Config::get('options/gzip', true) === true) {
                $tParms['content'] = ob_gzhandler(
                    $tParms['content'],
                    $uStatus
                ); // PHP_OUTPUT_HANDLER_START | PHP_OUTPUT_HANDLER_END
            }
        }

        return $tParms['content'];
    }

    /**
     * Terminates the execution of the framework.
     *
     * @param int       $uLevel         the exit status (0-254)
     * @param string    $uErrorMessage  the error message if available
     */
    public static function end($uLevel = 0, $uErrorMessage = null)
    {
        self::$exitStatus = array($uLevel, $uErrorMessage);
        ob_end_flush();

        exit($uLevel);
    }
}
