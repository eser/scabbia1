<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Objects;

use Scabbia\Extensions\Objects\Collection;

/**
 * Objects Extension: FileCollection Class
 *
 * @package Scabbia
 * @subpackage Objects
 * @version 1.1.0
 */
class FileCollection extends Collection
{
    /**
     * @ignore
     */
    public static function fromFile($uFile)
    {
        $tTemp = new static();
        $tTemp->add($uFile);

        return $tTemp;
    }

    /**
     * @ignore
     */
    public static function fromFiles()
    {
        $uFiles = func_get_args();
        if (is_array($uFiles[0])) {
            $uFiles = $uFiles[0];
        }

        $tTemp = new static();

        foreach ($uFiles as $tFile) {
            $tTemp->add($tFile);
        }

        return $tTemp;
    }

    /**
     * @ignore
     */
    public static function fromFileScan($uPattern)
    {
        $tSep = quotemeta(DIRECTORY_SEPARATOR);
        $tPos = strrpos($uPattern, $tSep);

        if ($tSep !== '/' && $tPos === false) {
            $tSep = '/';
            $tPos = strrpos($uPattern, $tSep);
        }

        if ($tPos !== false) {
            $tPattern = substr($uPattern, $tPos + strlen($tSep));
            $tPath = substr($uPattern, 0, $tPos + strlen($tSep));
        } else {
            $tPath = $uPattern;
            $tPattern = "";
        }

        $tTemp = new static();
        $tHandle = new \DirectoryIterator($tPath);
        $tPatExists = (strlen($uPattern) > 0);

        for (; $tHandle->valid(); $tHandle->next()) {
            if (!($tHandle->isFile())) {
                continue;
            }

            $tFile = $tHandle->current();

            if ($tPatExists && !fnmatch($tPattern, $tFile)) {
                continue;
            }

            $tTemp->add($tPath . $tFile);
        }

        return $tTemp;
    }
}
