<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\ICacheProvider;
use Scabbia\Extensions\Datasources\IDatasource;
use Scabbia\Io;
use Scabbia\Utils;

/**
 * Datasources Extension: FileSource class
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 *
 * @todo sanitize filenames
 */
class FileSource implements IDatasource, ICacheProvider, IStorageProvider
{
    /**
     * @ignore
     */
    public static $type = 'file';


    /**
     * @ignore
     */
    public $cacheTtl;
    /**
     * @ignore
     */
    public $storageTtl;
    /**
     * @ignore
     */
    public $keyphase;
    /**
     * @ignore
     */
    public $path;
    /**
     * @ignore
     */
    public $baseurl;


    /**
     * @ignore
     */
    public function __construct(array $uConfig)
    {
        $this->cacheTtl = isset($uConfig['cacheTtl']) ? $uConfig['cacheTtl'] : 120;
        $this->storageTtl = isset($uConfig['storageTtl']) ? $uConfig['storageTtl'] : -1;
        $this->keyphase = isset($uConfig['keyphase']) ? $uConfig['keyphase'] : '';
        $this->path = $uConfig['path'];
        $this->baseurl = isset($uConfig['baseurl']) ? $uConfig['baseurl'] : '';
    }

    /**
     * @ignore
     */
    public function baseUrl()
    {
        return Utils::translate($this->baseurl);
    }

    /**
     * @ignore
     */
    public function cacheGet($uKey)
    {
        // path
        $tPath = Io::translatePath($this->path . $uKey, true);

        if (!Io::isReadable($tPath)) {
            return false;
        }

        return Io::readSerialize($tPath, $this->keyphase);
    }

    /**
     * @ignore
     */
    public function cacheSet($uKey, $uObject)
    {
        // path
        $tPath = Io::translatePath($this->path . $uKey, true);

        Io::writeSerialize($tPath, $uObject, $this->keyphase);
    }

    /**
     * @ignore
     */
    public function cacheRemove($uKey)
    {
        // path
        $tPath = Io::translatePath($this->path . $uKey, true);

        Io::destroy($tPath);
    }

    /**
     * @ignore
     */
    public function cacheGarbageCollect()
    {
        // path
        $tPath = Io::translatePath($this->path, true);

        Io::garbageCollect($tPath, $this->cacheTtl);
    }

    /**
     * @ignore
     */
    public function storageGetUrl($uKey)
    {
        return Utils::translate($this->baseurl) . $uKey;
    }

    /**
     * @ignore
     */
    public function storageGet($uKey)
    {
        // path
        $tPath = Io::translatePath($this->path . $uKey, true);

        if (!Io::isReadable($tPath)) {
            return false;
        }

        return Io::readSerialize($tPath, $this->keyphase);
    }

    /**
     * @ignore
     */
    public function storagePut($uKey, $uObject)
    {
        // path
        $tPath = Io::translatePath($this->path . $uKey, true);

        Io::writeSerialize($tPath, $uObject, $this->keyphase);
    }

    /**
     * @ignore
     */
    public function storageReplace($uKey, $uObject)
    {
        // path
        $tPath = Io::translatePath($this->path . $uKey, true);

        Io::writeSerialize($tPath, $uObject, $this->keyphase);
    }

    /**
     * @ignore
     */
    public function storageRemove($uKey)
    {
        // path
        $tPath = Io::translatePath($this->path . $uKey, true);

        Io::destroy($tPath);
    }

    /**
     * @ignore
     */
    public function storageGarbageCollect()
    {
        if ($this->storageTtl > 0) {
            // path
            $tPath = Io::translatePath($this->path, true);

            Io::garbageCollect($tPath, $this->storageTtl);
        }
    }
}
