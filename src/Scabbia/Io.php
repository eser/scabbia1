<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Framework;
use Scabbia\Utils;

/**
 * Global input/output functions which helps framework execution.
 *
 * @package Scabbia
 * @version 1.1.0
 *
 * @todo serialize/unserialize data (example: resources)
 * @todo download garbage collection
 * @todo global event-based garbage collector
 * @todo download caching w/ aging
 * @todo purge
 */
class Io
{
    /**
     * @var int none
     */
    const GLOB_NONE = 0;
    /**
     * @var int recursive
     */
    const GLOB_RECURSIVE = 1;
    /**
     * @var int files
     */
    const GLOB_FILES = 2;
    /**
     * @var int directories
     */
    const GLOB_DIRECTORIES = 4;
    /**
     * @var int just names
     */
    const GLOB_JUSTNAMES = 8;


    /**
     * Reads from a file.
     *
     * @param string    $uPath  the file path
     * @param int       $uFlags io flags
     *
     * @return bool|string the file content
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
     * Writes to a file.
     *
     * @param string    $uPath      the file path
     * @param string    $uContent   the file content
     * @param int       $uFlags     io flags
     *
     * @return bool
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
     * Reads from a serialized file.
     *
     * @param string        $uPath      the file path
     * @param string|null   $uKeyphase  the key
     *
     * @return bool|mixed   the unserialized object
     */
    public static function readSerialize($uPath, $uKeyphase = null)
    {
        $tContent = self::read($uPath);

        //! ambiguous return value
        if ($tContent === false) {
            return false;
        }

        if (!is_null($uKeyphase) && strlen($uKeyphase) > 0) {
            $tContent = Utils::decrypt($tContent, $uKeyphase);
        }

        return unserialize($tContent);
    }

    /**
     * Serializes an object into a file.
     *
     * @param string        $uPath      the file path
     * @param string        $uContent   the file content
     * @param string|null   $uKeyphase  the key
     *
     * @return bool
     */
    public static function writeSerialize($uPath, $uContent, $uKeyphase = null)
    {
        $tContent = serialize($uContent);

        if (!is_null($uKeyphase) && strlen($uKeyphase) > 0) {
            $tContent = Utils::encrypt($tContent, $uKeyphase);
        }

        return self::write($uPath, $tContent);
    }

    /**
     * Updates a modification time of a file.
     *
     * @param string    $uPath      the file path
     *
     * @return bool
     */
    public static function touch($uPath)
    {
        return touch($uPath);
    }

    /**
     * Deletes a file.
     *
     * @param string    $uPath      the file path
     *
     * @return bool
     */
    public static function destroy($uPath)
    {
        if (file_exists($uPath)) {
            return unlink($uPath);
        }

        return false;
    }

    /**
     * Translates given framework-relative path to physical path.
     *
     * @param string    $uPath      the framework-relative path
     * @param string    $uBasePath
     *
     * @return string translated physical path
     */
    public static function translatePath($uPath, $uBasePath = null)
    {
        if (substr($uPath, 0, 6) == '{base}') {
            return Framework::$basepath . substr($uPath, 6);
        }

        if (substr($uPath, 0, 6) == '{core}') {
            return Framework::$corepath . substr($uPath, 6);
        }

        if (substr($uPath, 0, 8) == '{vendor}') {
            return Framework::$vendorpath . substr($uPath, 8);
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
     * Extracts a path from different path.
     *
     * @param string    $uPath      the full path
     * @param string    $uBasePath  path to extract
     *
     * @return string relative path
     */
    public static function extractPath($uPath, $uBasePath = null)
    {
        $uPath = strtr($uPath, DIRECTORY_SEPARATOR, '/');

        if (is_null($uBasePath)) {
            $uBasePath = Framework::$basepath;
        }

        $tLen = strlen($uBasePath);
        if (strncmp($uPath, $uBasePath, $tLen) == 0) {
            return substr($uPath, $tLen);
        }

        return $uPath;
    }

    /**
     * Determines the file is whether readable or not.
     *
     * @param string    $uFile  the relative path
     * @param int       $uAge   the time to live period in seconds
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
     * Reads the contents from cache file as long as it is not expired.
     * If the file is expired, invokes callback method and caches output.
     *
     * @param string        $uFile      the relative path
     * @param int           $uAge       the time to live period in seconds
     * @param callback|null $uCallback  the callback method
     *
     * @return mixed the result
     */
    public static function readFromCache($uFile, $uAge = -1, $uCallback = null) {
        $uFile = self::translatePath($uFile);

        if (self::isReadable($uFile, $uAge)) {
            return unserialize(file_get_contents($uFile));
        }

        if (is_null($uCallback)) {
            return null;
        }

        $tResult = call_user_func($uCallback);
        file_put_contents($uFile, serialize($tResult));

        return $tResult;
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
     * Garbage collects the given path
     *
     * @param string    $uPath  path
     * @param int       $uAge   age
     */
    public static function garbageCollect($uPath, $uAge = -1)
    {
        $tDirectory = new \DirectoryIterator($uPath);

        // age
        if ($uAge == -1) {
            $uAge = $this->defaultAge;
        }

        clearstatcache();
        foreach ($tDirectory as $tFile) {
            if (!$tFile->isFile()) {
                continue;
            }

            if (time() - $tFile->getMTime() < $uAge) {
                continue;
            }

            self::destroy($tFile->getPathname());
        }
    }

    /**
     * Downloads given file into framework's download directory.
     *
     * @param string    $uFile  filename in destination
     * @param string    $uUrl   url of source
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
     * @param string      $uPath            the path will be searched
     * @param string|null $uFilter          the pattern
     * @param int         $uOptions         the flags
     * @param string      $uRecursivePath   the path will be concatenated (recursive)
     * @param array       $uArray           the results array (recursive)
     *
     * @return array|bool the search results
     */
    public static function glob($uPath, $uFilter = null, $uOptions = self::GLOB_FILES, $uRecursivePath = '', array &$uArray = array())
    {
        $tPath = rtrim(strtr($uPath, DIRECTORY_SEPARATOR, '/'), '/') . '/';
        $tRecursivePath = $tPath . $uRecursivePath;

        // if(file_exists($tRecursivePath)) {
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
        // }

        $uArray = false;

        return $uArray;
    }
}
