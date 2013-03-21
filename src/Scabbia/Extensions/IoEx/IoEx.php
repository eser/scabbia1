<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\IoEx;

use Scabbia\Extensions\String\String;

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
    public static function read($uPath, $uFlags = LOCK_SH)
    {
        if (!is_readable($uPath)) {
            return false;
        }

        $tHandle = fopen($uPath, 'r', false);
        if ($tHandle === false) {
            return false;
        }

        $tLock = flock($tHandle, $uFlags);
        if ($tLock === false) {
            fclose($tHandle);

            return false;
        }

        $tContent = stream_get_contents($tHandle);
        flock($tHandle, LOCK_UN);
        fclose($tHandle);

        return $tContent;
    }

    /**
     * @ignore
     */
    public static function write($uPath, $uContent, $uFlags = LOCK_EX)
    {
        $tHandle = fopen($uPath, 'w', false);
        if ($tHandle === false) {
            return false;
        }

        if (flock($tHandle, $uFlags) === false) {
            fclose($tHandle);

            return false;
        }

        fwrite($tHandle, $uContent);
        fflush($tHandle);
        flock($tHandle, LOCK_UN);
        fclose($tHandle);

        return true;
    }

    /**
     * @ignore
     */
    public static function readSerialize($uPath, $uKeyphase = null)
    {
        $tContent = self::read($uPath);

        //! ambiguous return value
        if ($tContent === false) {
            return false;
        }

        if (!is_null($uKeyphase) && strlen($uKeyphase) > 0) {
            $tContent = String::decrypt($tContent, $uKeyphase);
        }

        return unserialize($tContent);
    }

    /**
     * @ignore
     */
    public static function writeSerialize($uPath, $uContent, $uKeyphase = null)
    {
        $tContent = serialize($uContent);

        if (!is_null($uKeyphase) && strlen($uKeyphase) > 0) {
            $tContent = String::encrypt($tContent, $uKeyphase);
        }

        return self::write($uPath, $tContent);
    }

    /**
     * @ignore
     */
    public static function touch($uPath)
    {
        return touch($uPath);
    }

    /**
     * @ignore
     */
    public static function destroy($uPath)
    {
        if (file_exists($uPath)) {
            return unlink($uPath);
        }

        return false;
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
