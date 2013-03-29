<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Cache;

use Scabbia\Extensions\IoEx\IoEx;
use Scabbia\Config;
use Scabbia\Framework;
use Scabbia\Io;

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
    public static $cacheTtl;
    /**
     * @ignore
     */
    public static $keyphase;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$cacheTtl = Config::get('cache/cacheTtl', 120);
        self::$keyphase = Config::get('cache/keyphase', '');
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
        $tPath = Io::translatePath('{writable}cache/' . $uFolder . IoEx::sanitize($uFilename), true);

        // age
        if ($uAge == -1) {
            $uAge = self::$cacheTtl;
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
        $tPath = Io::translatePath('{writable}cache/' . $uFolder . IoEx::sanitize($uFilename), true);

        // content
        Io::writeSerialize($tPath, $uObject, self::$keyphase);

        return $tPath;
    }
}
