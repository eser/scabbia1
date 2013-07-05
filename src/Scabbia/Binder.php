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
 *
 * @todo download bind-type
 */
class Binder
{
    /**
     * @var array Set of callback methods will be applied to files individually by types.
     */
    public static $fileProcessors = null;
    /**
     * @var array Set of callback methods will be applied generated pack by type.
     */
    public static $packProcessors = null;
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
        if (self::$fileProcessors === null) {
            self::$fileProcessors = array();
            self::$packProcessors = array();

            self::$fileProcessors['less'] = function ($uInput, $uDescription) {
                if (self::$lessCompiler === null) {
                    self::$lessCompiler = new \lessc();
                }

                $tContent  = '/* LESS ' . $uDescription . ' */' . PHP_EOL;
                $tContent .= self::$lessCompiler->compile($uInput);
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
                return self::printPhpSource($uInput);
            };
        }
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
        self::init();

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
            if ($tPart['class'] !== null && !in_array($tPart['class'], $this->classes, true)) {
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

    /**
     * Returns a php source to view.
     *
     * @param string    $uInput         php source code
     * @param bool      $uOnlyContent   returns just file content without comments
     *
     * @return array|string the file content in printable format with or without comments
     */
    public static function printPhpSource($uInput, $uOnlyContent = true)
    {
        $tDocComments = array();
        $tReturn = '';
        $tLastToken = -1;
        $tOpenStack = array();

        foreach (token_get_all($uInput) as $tToken) {
            if (is_array($tToken)) {
                $tTokenId = $tToken[0];
                $tTokenContent = $tToken[1];
            } else {
                $tTokenId = null;
                $tTokenContent = $tToken;
            }

            // $tReturn .= PHP_EOL . token_name($tTokenId) . PHP_EOL;
            switch ($tTokenId) {
                case T_OPEN_TAG:
                    $tReturn .= '<' . '?php ';
                    array_push($tOpenStack, $tTokenId);
                    break;
                case T_OPEN_TAG_WITH_ECHO:
                    $tReturn .= '<' . '?php echo ';
                    array_push($tOpenStack, $tTokenId);
                    break;
                case T_CLOSE_TAG:
                    $tLastOpen = array_pop($tOpenStack);

                    if ($tLastOpen == T_OPEN_TAG_WITH_ECHO) {
                        $tReturn .= '; ';
                    } else {
                        if ($tLastToken != T_WHITESPACE) {
                            $tReturn .= ' ';
                        }
                    }

                    $tReturn .= '?' . '>';
                    break;
                case T_COMMENT:
                case T_DOC_COMMENT:
                    if (substr($tTokenContent, 0, 3) == '/**') {
                        $tCommentContent = substr($tTokenContent, 2, strlen($tTokenContent) - 4);

                        foreach (explode("\n", $tCommentContent) as $tLine) {
                            $tLineContent = ltrim($tLine, "\t ");

                            if (substr($tLineContent, 0, 3) == '* @') {
                                $tLineContents = explode(' ', substr($tLineContent, 3), 2);
                                if (count($tLineContents) < 2) {
                                    continue;
                                }

                                if (!isset($tDocComments[$tLineContents[0]])) {
                                    $tDocComments[$tLineContents[0]] = array();
                                }

                                $tDocComments[$tLineContents[0]][] = $tLineContents[1];
                            }
                        }
                    }
                    break;
                case T_WHITESPACE:
                    if ($tLastToken != T_WHITESPACE &&
                        $tLastToken != T_OPEN_TAG &&
                        $tLastToken != T_OPEN_TAG_WITH_ECHO &&
                        $tLastToken != T_COMMENT &&
                        $tLastToken != T_DOC_COMMENT
                    ) {
                        $tReturn .= ' ';
                    }
                    break;
                case null:
                    $tReturn .= $tTokenContent;
                    if ($tLastToken == T_END_HEREDOC) {
                        $tReturn .= "\n";
                        $tTokenId = T_WHITESPACE;
                    }
                    break;
                default:
                    $tReturn .= $tTokenContent;
                    break;
            }

            $tLastToken = $tTokenId;
        }

        while (count($tOpenStack) > 0) {
            $tLastOpen = array_pop($tOpenStack);
            if ($tLastOpen == T_OPEN_TAG_WITH_ECHO) {
                $tReturn .= '; ';
            } else {
                if ($tLastToken != T_WHITESPACE) {
                    $tReturn .= ' ';
                }
            }

            $tReturn .= '?' . '>';
        }

        if (!$uOnlyContent) {
            $tArray = array(&$tReturn, $tDocComments);

            return $tArray;
        }

        return $tReturn;
    }
}
