<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Framework;

/**
 * Global utility functions which helps framework execution.
 *
 * @package Scabbia
 *
 * @todo serialize/unserialize data (example: resources)
 * @todo download garbage collection
 * @todo global event-based garbage collector
 * @todo download caching w/ aging
 */
class Utils
{
    const GLOB_NONE = 0;
    const GLOB_RECURSIVE = 1;
    const GLOB_FILES = 2;
    const GLOB_DIRECTORIES = 4;
    const GLOB_JUSTNAMES = 8;


    /**
     * Presets which can be used in regular expression
     */
    public static $regexpPresets = array(
        'num' => '[0-9]+',
        'num?' => '[0-9]*',
        'alnum' => '[a-zA-Z0-9]+',
        'alnum?' => '[a-zA-Z0-9]*',
        'any' => '[a-zA-Z0-9\.\-_%=]+', // ~
        'any?' => '[a-zA-Z0-9\.\-_%=]*',
        'all' => '.+',
        'all?' => '.*'
    );


    /**
     * Checks the given framework version is greater than running one.
     *
     * @param string $uVersion framework version
     *
     * @return bool running framework version is greater than parameter
     */
    public static function version($uVersion)
    {
        return version_compare(Framework::VERSION, $uVersion, '>=');
    }

    /**
     * Checks the given php version is greater than running one.
     *
     * @param string $uVersion php version
     *
     * @return bool running php version is greater than parameter
     */
    public static function phpVersion($uVersion)
    {
        return version_compare(PHP_VERSION, $uVersion, '>=');
    }

    /**
     * Translates given framework-relative path to physical path.
     *
     * @param string $uPath the framework-relative path
     * @param string $uBasePath
     *
     * @return string translated physical path
     */
    public static function translatePath($uPath, $uBasePath = null)
    {
        if (substr($uPath, 0, 6) == '{base}') {
            return Framework::$basepath . substr($uPath, 6);
        }

        if (substr($uPath, 0, 6) == '{core}') {
            return Framework::$corepath . substr($uPath, 8);
        }

        if (substr($uPath, 0, 5) == '{app}') {
            return Framework::$apppath . substr($uPath, 5);
        }

        if (is_null($uBasePath)) {
            return $uPath;
        }

        return $uBasePath . $uPath;
    }

    /**
     * Determines the file is whether readable or not.
     *
     * @param string $uFile the relative path
     * @param int $uAge the time to live period in seconds
     *
     * @return bool the result
     */
    public static function isReadable($uFile, $uAge = -1)
    {
        if (!file_exists($uFile)) {
            return false;
        }

        if ($uAge >= 0 && (time() - filemtime($uFile) >= $uAge)) {
            return false;
        }

        return true;
    }

    /**
     * Locates the writable path and concatenates it with given relative path.
     *
     * @param string $uFile         the relative path
     * @param bool   $uCreateFolder creates path if does not exist
     *
     * @throws \Exception
     * @return string the physical path
     */
    public static function writablePath($uFile = '', $uCreateFolder = false)
    {
        $tPathConcat = Framework::$apppath . 'writable/' . $uFile;

        if ($uCreateFolder) {
            $tPathDirectory = pathinfo($tPathConcat, PATHINFO_DIRNAME);

            if (!is_dir($tPathDirectory)) {
                if (Framework::$readonly) {
                    throw new \Exception($tPathDirectory . ' does not exists.');
                }

                mkdir($tPathDirectory, 0777, true);
            }
        }

        return $tPathConcat;
    }

    /**
     * Downloads given file into framework's download directory.
     *
     * @param $uFile string filename in destination
     * @param $uUrl string url of source
     *
     * @return bool whether the file is downloaded or not
     */
    public static function downloadFile($uFile, $uUrl)
    {
        $tUrlHandle = fopen($uUrl, 'rb', false);
        if ($tUrlHandle === false) {
            return false;
        }

        $tHandle = fopen(self::writablePath('downloaded/' . $uFile), 'wb', false);
        if ($tHandle === false) {
            fclose($tUrlHandle);

            return false;
        }

        if (flock($tHandle, LOCK_EX) === false) {
            fclose($tHandle);
            fclose($tUrlHandle);

            return false;
        }

        stream_copy_to_stream($tUrlHandle, $tHandle);
        fflush($tHandle);
        flock($tHandle, LOCK_UN);
        fclose($tHandle);

        fclose($tUrlHandle);

        return true;
    }

    /**
     * Returns a php file source to view.
     *
     * @param string $uInput string path of source file
     * @param bool $uOnlyContent returns just file content without comments
     *
     * @return array|string the file content in printable format with or without comments
     */
    public static function printFile($uInput, $uOnlyContent = true)
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

    /**
     * Returns a php file source to view.
     *
     * @param string      $uPath    the path will be searched
     * @param string|null $uFilter  the pattern
     * @param int         $uOptions the flags
     * @param string      $uRecursivePath the path will be concatenated (recursive)
     * @param array       $uArray the results array (recursive)
     *
     * @return array|bool the search results
     */
    public static function glob($uPath, $uFilter = null, $uOptions = self::GLOB_FILES, $uRecursivePath = '', &$uArray = array())
    {
        $tPath = rtrim(strtr($uPath, DIRECTORY_SEPARATOR, '/'), '/') . '/';
        $tRecursivePath = $tPath . $uRecursivePath;

        try {
            $tDir = new \DirectoryIterator($tRecursivePath);

            foreach ($tDir as $tFile) {
                $tFileName = $tFile->getFilename();

                if ($tFileName[0] == '.') { // $tFile->isDot()
                    continue;
                }

                if ($tFile->isDir()) {
                    $tDirectory = $uRecursivePath . $tFileName . '/';

                    if (($uOptions & self::GLOB_DIRECTORIES) > 0) {
                        $uArray[] = (($uOptions & self::GLOB_JUSTNAMES) > 0) ? $tDirectory : $tPath . $tDirectory;
                    }

                    if (($uOptions & self::GLOB_RECURSIVE) > 0) {
                        self::glob(
                            $tPath,
                            $uFilter,
                            $uOptions,
                            $tDirectory,
                            $uArray
                        );
                    }

                    continue;
                }

                if (($uOptions & self::GLOB_FILES) > 0 && $tFile->isFile()) {
                    if (is_null($uFilter) || fnmatch($uFilter, $tFileName)) {
                        $uArray[] = (($uOptions & self::GLOB_JUSTNAMES) > 0) ? $uRecursivePath . $tFileName : $tRecursivePath . $tFileName;
                    }

                    continue;
                }
            }

            return $uArray;
        } catch (\Exception $tException) {
            // echo $tException->getMessage();
        }

        $uArray = false;

        return $uArray;
    }

    /**
     * Converts presets to the regular expressions.
     *
     * @param string $uPattern the pattern to search for, as a string
     *
     * @return string generated regular expression
     */
    private static function pregFormat($uPattern)
    {
        $tBuffer = array(array(false, ''));
        $tBrackets = 0;

        for ($tPos = 0, $tLen = strlen($uPattern); $tPos < $tLen; $tPos++) {
            $tChar = substr($uPattern, $tPos, 1);

            if ($tChar == '\\') {
                $tBuffer[$tBrackets][1] .= substr($uPattern, ++$tPos, 1);
                continue;
            }

            if ($tChar == '(') {
                $tBuffer[++$tBrackets] = array(false, '');
                continue;
            }

            if ($tBrackets > 0) {
                if ($tChar == ':' && $tBuffer[$tBrackets][0] === false) {
                    $tBuffer[$tBrackets][0] = $tBuffer[$tBrackets][1];
                    $tBuffer[$tBrackets][1] = '';

                    continue;
                }

                if ($tChar == ')') {
                    --$tBrackets;
                    $tLast = array_pop($tBuffer);

                    if ($tLast[0] === false) {
                        $tBuffer[$tBrackets][1] .= '(?:';
                    } else {
                        $tBuffer[$tBrackets][1] .= '(?P<' . $tLast[0] . '>';
                    }

                    if (array_key_exists($tLast[1], self::$regexpPresets)) {
                        $tBuffer[$tBrackets][1] .= self::$regexpPresets[$tLast[1]] . ')';
                    } else {
                        $tBuffer[$tBrackets][1] .= $tLast[1] . ')';
                    }

                    continue;
                }
            }

            if ($tChar == ')') {
                $tBuffer[$tBrackets][1] .= '\\)';
                continue;
            }

            $tBuffer[$tBrackets][1] .= $tChar;
        }

        while ($tBrackets > 0) {
            --$tBrackets;
            $tLast = array_pop($tBuffer);
            $tBuffer[0][1] .= '\\(' . $tLast[1];
        }

        return $tBuffer[0][1];
    }

    /**
     * Searches subject for a match to the regular expression given in pattern.
     *
     * @param string $uPattern the pattern to search for, as a string
     * @param string $uSubject the input string
     * @param string $uModifiers the PCRE modifiers
     *
     * @return array the matches
     */
    public static function pregMatch($uPattern, $uSubject, $uModifiers = '^')
    {
        $tPattern = self::pregFormat($uPattern);

        if (strpos($uModifiers, '^') === 0) {
            preg_match('#^' . $tPattern . '$#' . substr($uModifiers, 1), $uSubject, $tResult);
        } else {
            preg_match('#' . $tPattern . '#' . $uModifiers, $uSubject, $tResult);
        }

        // if (count($tResult) > 0) {
        //    return $tResult;
        // }
        //
        // return false;

        return $tResult;
    }

    /**
     * Replaces subject with the matches of the regular expression given in pattern.
     *
     * @param string $uPattern the pattern to search for, as a string
     * @param string $uReplacement the replacement string
     * @param string $uSubject the string or an array with strings to replace
     * @param string $uModifiers the PCRE modifiers
     *
     * @return array the result of replace operation
     */
    public static function pregReplace($uPattern, $uReplacement, $uSubject, $uModifiers = '^')
    {
        $tPattern = self::pregFormat($uPattern);

        if (strpos($uModifiers, '^') === 0) {
            $tResult = preg_replace('#^' . $tPattern . '$#' . substr($uModifiers, 1), $uReplacement, $uSubject, -1, $tCount);
        } else {
            $tResult = preg_replace('#' . $tPattern . '#' . $uModifiers, $uReplacement, $uSubject, -1, $tCount);
        }

        if ($tCount > 0) {
            return $tResult;
        }

        return false;
    }
}