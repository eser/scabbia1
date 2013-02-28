<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\Datasources;

/**
 * Datasources Extension: Datasource Class
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
abstract class Datasource
{
    /**
     * @ignore
     */
    public $id;
    /**
     * @ignore
     */
    public $provider;
    /**
     * @ignore
     */
    public $cache = array();
    /**
     * @ignore
     */
    public $stats = array('cache' => 0, 'query' => 0);


    /**
     * @ignore
     */
    public function __construct($uConfig)
    {
        $this->id = $uConfig['id'];

        $this->provider = new Datasources::$types[$uConfig['type']]['provider'] ($uConfig);
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @ignore
     */
    public function open()
    {
        $this->provider->open();
    }

    /**
     * @ignore
     */
    public function close()
    {
        $this->provider->close();
        $this->provider = null;
    }

    /**
     * @ignore
     */
    public function serverInfo()
    {
        $this->open();

        return $this->provider->serverInfo();
    }
}
