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

/**
 * Datasources Extension: FileSource class
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 *
 * @todo sanitize filenames
 */
class FileSource implements IDatasource, ICacheProvider
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
    public $keyphase;
    /**
     * @ignore
     */
    public $path;


    /**
     * @ignore
     */
    public function __construct(array $uConfig)
    {
        $this->cacheTtl = isset($uConfig['cacheTtl']) ? $uConfig['cacheTtl'] : 120;
        $this->keyphase = isset($uConfig['keyphase']) ? $uConfig['keyphase'] : '';
        $this->path = $uConfig['path'];
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
}
