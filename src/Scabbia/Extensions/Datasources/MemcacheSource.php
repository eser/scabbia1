<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\ICacheProvider;
use Scabbia\Extensions\Datasources\IDatasource;
use Scabbia\Extensions\Datasources\IServerConnection;

/**
 * Datasources Extension: MemcacheSource class
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
class MemcacheSource implements IDatasource, ICacheProvider, IServerConnection
{
    /**
     * @ignore
     */
    public static $type = 'memcache';


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
    public $storage = null;
    /**
     * @ignore
     */
    public $connection = null;


    /**
     * @ignore
     */
    public function __construct(array $uConfig)
    {
        $this->defaultAge = isset($uConfig['defaultAge']) ? intval($uConfig['defaultAge']) : 120;
        $this->keyphase = isset($uConfig['keyphase']) ? $uConfig['keyphase'] : '';
        $this->storage = isset($uConfig['storage']) ? parse_url($uConfig['storage']) : null;
    }

    /**
     * @ignore
     */
    public function connectionOpen()
    {
        if (!is_null($this->connection)) {
            return;
        }

        if ($this->storage['scheme'] == 'memcache' && extension_loaded('memcache')) {
            $this->connection = new \Memcache();
            $this->connection->connect($this->storage['host'], $this->storage['port']);

            return;
        }
    }

    /**
     * @ignore
     */
    public function connectionClose() {
        $this->connection->close();
    }

    /**
     * @ignore
     */
    public function serverInfo() {
        return $this->connection->getVersion();
    }


    /**
     * @ignore
     */
    public function cacheGet($uKey)
    {
        $this->openConnection();

        return $this->connection->get($uKey);
    }

    /**
     * @ignore
     */
    public function cacheSet($uKey, $uObject)
    {
        $this->openConnection();

        $this->connection->set($uKey, $uObject, 0, $this->defaultAge);
    }

    /**
     * @ignore
     */
    public function cacheRemove($uKey)
    {
        $this->openConnection();

        $this->connection->delete($uKey);
    }

    /**
     * @ignore
     */
    public function cacheGarbageCollect()
    {
    }
}
