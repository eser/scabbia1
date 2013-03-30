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
 * @todo imageCheckDimensions, imageCheckSize
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
    public static $cacheTtl;


    /**
     * @ignore
     */
    public static function extensionLoad()
    {
        self::$cachePath = Io::translatePath('{writable}cache/media/', true);
        self::$cacheTtl = (int)Config::get('media/cacheTtl', 120);
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
}
