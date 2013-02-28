<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Cache;

use Scabbia\Extensions\Io\Io;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Utils;

/**
 * Cache Extension
 *
 * @package Scabbia
 * @subpackage Cache
 * @version 1.1.0
 */
class Cache
{
    /**
     * @ignore
     */
    public static $defaultAge;
    /**
     * @ignore
     */
    public static $keyphase;
    /**
     * @ignore
     */
    public static $storage = null;
    /**
     * @ignore
     */
    public static $storageObject = null;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$defaultAge = intval(Config::get('cache/defaultAge', '120'));
        self::$keyphase = Config::get('cache/keyphase', '');

        $tStorage = Config::get('cache/storage', '');
        if (strlen($tStorage) > 0) {
            self::$storage = parse_url($tStorage);
        }
    }

    /**
     * @ignore
     */
    public static function storageOpen()
    {
        if (!is_null(self::$storageObject)) {
            return;
        }

        if (self::$storage['scheme'] == 'memcache' && extension_loaded('memcache')) {
            self::$storageObject = new \Memcache();
            self::$storageObject->connect(self::$storage['host'], self::$storage['port']);

            return;
        }
    }

    /**
     * @ignore
     */
    public static function storageGet($uKey)
    {
        self::storageOpen();

        return self::$storageObject->get($uKey);
    }

    /**
     * @ignore
     */
    public static function storageSet($uKey, $uValue, $uAge = -1)
    {
        self::storageOpen();

        // age
        if ($uAge == -1) {
            $uAge = self::$defaultAge;
        }

        self::$storageObject->set($uKey, $uValue, 0, $uAge);
    }

    /**
     * @ignore
     */
    public static function storageDestroy($uKey)
    {
        self::storageOpen();

        self::$storageObject->delete($uKey);
    }

    /**
     * @ignore
     */
    public static function filePath($uFolder, $uFilename, $uAge = -1)
    {
        if (Framework::$readonly) {
            return array(false, null);
        }

        // path
        $tPath = Utils::writablePath('cache/' . $uFolder . Io::sanitize($uFilename), true);

        // age
        if ($uAge == -1) {
            $uAge = self::$defaultAge;
        }

        // check
        if (!file_exists($tPath) ||
            ($uAge != 0 && time() - filemtime($tPath) >= $uAge)
        ) {
            return array(false, $tPath);
        }

        return array(true, $tPath);
    }

    /**
     * @ignore
     */
    public static function fileGet($uFolder, $uFilename, $uAge = -1)
    {
        // path
        $tPath = self::filePath($uFolder, $uFilename, $uAge);

        //! ambiguous return value
        if (!$tPath[0]) {
            return false;
        }

        // content
        return Io::readSerialize($tPath[1], self::$keyphase);
    }

    /**
     * @ignore
     */
    public static function fileGetUrl($uKey, $uUrl, $uAge = -1)
    {
        $tFile = self::filePath('url/', $uKey, $uAge);

        if (!$tFile[0]) {
            $tContent = file_get_contents($uUrl);
            Io::write($tFile[1], $tContent);

            return $tContent;
        }

        return Io::read($tFile[1]);
    }

    /**
     * @ignore
     */
    public static function fileSet($uFolder, $uFilename, $uObject)
    {
        // path
        $tPath = Utils::writablePath('cache/' . $uFolder . Io::sanitize($uFilename), true);

        // content
        Io::writeSerialize($tPath, $uObject, self::$keyphase);

        return $tPath;
    }

    /**
     * @ignore
     */
    public static function fileDestroy($uFolder, $uFilename)
    {
        $tPath = Utils::writablePath('cache/' . $uFolder, true);
        Io::destroy($tPath . Io::sanitize($uFilename));
    }

    /**
     * @ignore
     */
    public static function fileGarbageCollect($uFolder, $uAge = -1)
    {
        // path
        $tPath = Utils::writablePath('cache/' . $uFolder, true);
        $tDirectory = new \DirectoryIterator($tPath);

        // age
        if ($uAge == -1) {
            $uAge = self::$defaultAge;
        }

        clearstatcache();
        foreach ($tDirectory as $tFile) {
            if (!$tFile->isFile()) {
                continue;
            }

            if (time() - $tFile->getMTime() < $uAge) {
                continue;
            }

            Io::destroy($tFile->getPathname());
        }
    }
}
