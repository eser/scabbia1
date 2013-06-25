<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Io;

/**
 * Binder combines multiple files into a file.
 *
 * @package Scabbia
 * @version 1.1.0
 */
class Binder
{
    /**
     * @var array Set of callback methods will be applied to files individually by types.
     */
    public static $fileProcessors = array();
    /**
     * @var array Set of callback methods will be applied generated pack by type.
     */
    public static $packProcessors = array();
    /**
     * @var object LESS compiler instance.
     */
    public static $lessCompiler = null;


    /**
     * @var array Set of parts.
     */
    public $parts = array();
    /**
     * @var string Name of the output.
     */
    public $outputName;
    /**
     * @var string Type of the output.
     */
    public $outputType;
    /**
     * @var string Class list.
     */
    public $classes;
    /**
     * @var int    Cache Time to live.
     */
    public $cacheTtl;


    /**
     * Static initialization of binder class.
     */
    public static function init()
    {
        self::$fileProcessors['less'] = function ($uInput, $uDescription) {
            if (is_null(self::$lessCompiler)) {
                self::$lessCompiler = new \lessc();
            }

            $tContent  = '/* LESS ' . $uDescription . ' */' . PHP_EOL;
            $tContent .= self::$lessCompiler->compileFile($uInput);
            $tContent .= PHP_EOL;

            return $tContent;
        };

        self::$fileProcessors['css'] = function ($uInput, $uDescription) {
            $tContent  = '/* CSS ' . $uDescription . ' */' . PHP_EOL;
            $tContent .= $uInput;
            $tContent .= PHP_EOL;

            return $tContent;
        };

        self::$fileProcessors['js'] = function ($uInput, $uDescription) {
            $tContent  = '/* JS ' . $uDescription . ' */' . PHP_EOL;
            $tContent .= $uInput;
            $tContent .= PHP_EOL;

            return $tContent;
        };

        self::$fileProcessors['php'] = function ($uInput, $uDescription) {
            return Utils::printFile($uInput);
        };
    }


    /**
     * Constructs a new instance of binder.
     *
     * @param string $uOutputName Name of the output
     * @param string $uOutputType Type of the output
     * @param int    $uCacheTtl   Cache time to live
     * @param array  $uClasses    Classes
     */
    public function __construct($uOutputName, $uOutputType, $uCacheTtl = 0, array $uClasses = array())
    {
        $this->outputName = $uOutputName;
        $this->outputType = $uOutputType;
        $this->cacheTtl = $uCacheTtl;
        $this->classes = $uClasses;
    }

    /**
     * Adds a new part to binder instance.
     *
     * @param string $uBindType Binding type, available options are function, string and file
     * @param string $uPartType Type of the part
     * @param string $uValue Value
     * @param string $uClass Class name
     */
    public function add($uBindType, $uPartType, $uValue, $uClass = null)
    {
        $this->parts[] = array(
            'bindtype' => $uBindType,
            'parttype' => $uPartType,
            'class' => $uClass,
            'value' => $uValue
        );
    }

    /**
     * Outputs all parts in single output.
     *
     * @returns string Output
     */
    public function output()
    {
        $tOutputFile = Io::translatePath('{writable}cache/assets/' . $this->outputName);
        foreach ($this->classes as $tClassName) {
            $tOutputFile .= '_' . $tClassName;
        }
        $tOutputFile .= '.' . $this->outputType;

        if (!Framework::$disableCaches && Io::isReadable($tOutputFile, $this->cacheTtl)) {
            return Io::read($tOutputFile);
        }

        $tContent = '';

        foreach ($this->parts as $tPart) {
            if (!is_null($tPart['class']) && !in_array($tPart['class'], $this->classes, true)) {
                continue;
            }

            if ($tPart['bindtype'] == 'function') {
                $tValue = call_user_func($tPart['value'], $tPart);
                $tDescription = 'function ' . $tPart['value'];
            } elseif ($tPart['bindtype'] == 'string') {
                $tValue = $tPart['value'];
                $tDescription = 'string';
            } else {
                $tValue = Io::read(Io::translatePath($tPart['value']));
                $tDescription = 'file ' . $tPart['value'];
            }

            if (array_key_exists($tPart['parttype'], self::$fileProcessors)) {
                $tContent .= call_user_func(
                    self::$fileProcessors[$tPart['parttype']],
                    $tValue,
                    $tDescription
                );
            } else {
                $tContent .= $tValue;
            }

        }

        if (array_key_exists($this->outputType, self::$packProcessors)) {
            $tContent = call_user_func(self::$packProcessors[$this->outputType], $tContent);
        }

        if (!Framework::$readonly) {
            Io::write($tOutputFile, $tContent);
        }

        return $tContent;
    }
}
