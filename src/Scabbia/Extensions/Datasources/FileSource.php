<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
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
    public $defaultAge;
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
        $this->defaultAge = isset($uConfig['defaultAge']) ? intval($uConfig['defaultAge']) : 120;
        $this->keyphase = isset($uConfig['keyphase']) ? $uConfig['keyphase'] : '';
        $this->path = isset($uConfig['path']) ? parse_url($uConfig['path']) : null;
    }

    /**
     * @ignore
     */
    public function cacheGet($uKey)
    {
        // path
        $tPath = Io::writablePath($this->path . $uKey, true);

        return Io::readSerialize($tPath, $this->keyphase);
    }

    /**
     * @ignore
     */
    public function cacheSet($uKey, $uObject)
    {
        // path
        $tPath = Io::writablePath($this->path . $uKey, true);

        Io::writeSerialize($tPath, $uObject, $this->keyphase);
    }

    /**
     * @ignore
     */
    public function cacheRemove($uKey)
    {
        // path
        $tPath = Io::writablePath($this->path . $uKey, true);

        Io::destroy($tPath);
    }

    /**
     * @ignore
     */
    public function cacheGarbageCollect()
    {
        // path
        $tPath = Io::writablePath($this->path, true);

        Io::garbageCollect($tPath, $this->defaultAge);
    }
}
