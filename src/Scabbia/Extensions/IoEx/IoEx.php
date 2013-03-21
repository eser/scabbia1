<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\IoEx;

use Scabbia\Extensions\String\String;
use Scabbia\Utils;

/**
 * IoEx Extension
 *
 * @package Scabbia
 * @subpackage IoEx
 * @version 1.1.0
 */
class IoEx
{
    /**
     * @ignore
     */
    public static function map($uPath, $uPattern = null, $uRecursive = true, $uBasenames = false)
    {
        $tArray = array('.' => array());
        $tDir = new \DirectoryIterator($uPath);

        foreach ($tDir as $tFile) {
            $tFileName = $tFile->getFilename();

            if ($tFileName[0] == '.') { // $tFile->isDot()
                continue;
            }

            if ($tFile->isDir()) {
                if ($uRecursive) {
                    $tArray[$tFileName] = self::map($uPath . '/' . $tFileName, $uPattern, true, $uBasenames);
                    continue;
                }

                $tArray[$tFileName] = null;
                continue;
            }

            if ($tFile->isFile() && (is_null($uPattern) || fnmatch($uPattern, $tFileName))) {
                $tArray['.'][] = ($uBasenames ? pathinfo($tFileName, PATHINFO_FILENAME) : $tFileName);
            }
        }

        return $tArray;
    }

    /**
     * @ignore
     */
    public static function mapFlatten($uPath, $uPattern = null, $uRecursive = true, $uBasenames = false, &$uArray = null, $uPrefix = '')
    {
        if (is_null($uArray)) {
            $uArray = array();
        }

        $tDir = new \DirectoryIterator($uPath);

        foreach ($tDir as $tFile) {
            $tFileName = $tFile->getFilename();

            if ($tFileName[0] == '.') { // $tFile->isDot()
                continue;
            }

            if ($tFile->isDir()) {
                if ($uRecursive) {
                    $tDirectory = $uPrefix . $tFileName . '/';
                    // $uArray[] = $tDirectory;
                    self::mapFlatten($uPath . '/' . $tFileName, $uPattern, true, $uBasenames, $uArray, $tDirectory);
                }

                continue;
            }

            if ($tFile->isFile() && (is_null($uPattern) || fnmatch($uPattern, $tFileName))) {
                $uArray[] = $uPrefix . ($uBasenames ? pathinfo($tFileName, PATHINFO_FILENAME) : $tFileName);
            }
        }

        return $uArray;
    }

    /**
     * @ignore
     */
    public static function sanitize($uFilename, $uRemoveAccent = false, $uRemoveSpaces = false)
    {
        static $sReplaceChars = array('\\' => '-', '/' => '-', ':' => '-', '?' => '-', '*' => '-', '"' => '-', '\'' => '-', '<' => '-', '>' => '-', '|' => '-', '.' => '-', '+' => '-');

        $tPathInfo = pathinfo($uFilename);
        $tFilename = strtr($tPathInfo['filename'], $sReplaceChars);

        if (isset($tPathInfo['extension'])) {
            $tFilename .= '.' . strtr($tPathInfo['extension'], $sReplaceChars);
        }

        $tFilename = String::removeInvisibles($tFilename);
        if ($uRemoveAccent) {
            $tFilename = String::removeAccent($tFilename);
        }

        if ($uRemoveSpaces) {
            $tFilename = strtr($tFilename, ' ', '_');
        }

        if (isset($tPathInfo['dirname']) && $tPathInfo['dirname'] != '.') {
            return rtrim(strtr($tPathInfo['dirname'], DIRECTORY_SEPARATOR, '/'), '/') . '/' . $tFilename;
        }

        return $tFilename;
    }
}
