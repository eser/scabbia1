<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Helpers;

/**
 * Helpers Extension: FileSystem Class
 *
 * @package Scabbia
 * @subpackage Helpers
 * @version 1.1.0
 */
class FileSystem
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

            if ($tFileName[0] === '.') { // $tFile->isDot()
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

            if ($tFile->isFile() && ($uPattern === null || fnmatch($uPattern, $tFileName))) {
                $tArray['.'][] = ($uBasenames ? pathinfo($tFileName, PATHINFO_FILENAME) : $tFileName);
            }
        }

        return $tArray;
    }

    /**
     * @ignore
     */
    public static function mapFlatten(
        $uPath,
        $uPattern = null,
        $uRecursive = true,
        $uBasenames = false,
        &$uArray = null,
        $uPrefix = ""
    ) {
        if ($uArray === null) {
            $uArray = array();
        }

        $tDir = new \DirectoryIterator($uPath);

        foreach ($tDir as $tFile) {
            $tFileName = $tFile->getFilename();

            if ($tFileName[0] === '.') { // $tFile->isDot()
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

            if ($tFile->isFile() && ($uPattern === null || fnmatch($uPattern, $tFileName))) {
                $uArray[] = $uPrefix . ($uBasenames ? pathinfo($tFileName, PATHINFO_FILENAME) : $tFileName);
            }
        }

        return $uArray;
    }
}
