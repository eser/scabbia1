<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
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
 *
 * @todo addServer support
 * @todo pconnect option
 * @todo replace?
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
    public $cacheTtl;
    /**
     * @ignore
     */
    public $keyphase;
    /**
     * @ignore
     */
    public $host;
    /**
     * @ignore
     */
    public $port;
    /**
     * @ignore
     */
    public $connection = null;


    /**
     * @ignore
     */
    public function __construct(array $uConfig)
    {
        $this->cacheTtl = isset($uConfig['cacheTtl']) ? $uConfig['cacheTtl'] : 120;
        $this->keyphase = isset($uConfig['keyphase']) ? $uConfig['keyphase'] : '';
        $this->host = $uConfig['host'];
        $this->port = $uConfig['port'];
    }

    /**
     * @ignore
     */
    public function connectionOpen()
    {
        if (!is_null($this->connection)) {
            return;
        }

        $this->connection = new \Memcache();
        $this->connection->connect($this->host, $this->port);
    }

    /**
     * @ignore
     */
    public function connectionClose()
    {
        $this->connection->close();
    }

    /**
     * @ignore
     */
    public function serverInfo()
    {
        return $this->connection->getVersion();
    }


    /**
     * @ignore
     */
    public function cacheGet($uKey)
    {
        $this->connectionOpen();

        return $this->connection->get($uKey);
    }

    /**
     * @ignore
     */
    public function cacheSet($uKey, $uObject)
    {
        $this->connectionOpen();

        $this->connection->set($uKey, $uObject, 0, $this->cacheTtl);
    }

    /**
     * @ignore
     */
    public function cacheRemove($uKey)
    {
        $this->connectionOpen();

        $this->connection->delete($uKey);
    }

    /**
     * @ignore
     */
    public function cacheGarbageCollect()
    {
    }
}
