<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Media;

use Scabbia\Extensions\Media\MediaFile;
use Scabbia\Config;
use Scabbia\Io;

/**
 * Media Extension
 *
 * @package Scabbia
 * @subpackage Media
 * @version 1.1.0
 *
 * @todo add watermark
 * @todo write text w/ truetype fonts
 * @todo integrate with cache extension
 */
class Media
{
    /**
     * @ignore
     */
    public static $cachePath;
    /**
     * @ignore
     */
    public static $cacheAge;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$cachePath = Io::translatePath('{writable}cache/media/', true);
        self::$cacheAge = intval(Config::get('media/cacheAge', '120'));
    }

    /**
     * @ignore
     */
    public static function open($uSource, $uOriginalFilename = null)
    {
        return new MediaFile($uSource, $uOriginalFilename);
    }

    /**
     * @ignore
     */
    public static function calculateHash()
    {
        $uArgs = func_get_args();

        return implode('_', $uArgs);
    }

    /**
     * @ignore
     */
    public static function garbageCollect()
    {
        $tDirectory = new \DirectoryIterator(self::$cachePath);

        clearstatcache();
        foreach ($tDirectory as $tFile) {
            if (!$tFile->isFile()) {
                continue;
            }

            if (time() - $tFile->getMTime() < self::$cacheAge) {
                continue;
            }

            unlink($tFile->getPathname());
        }
    }
}
