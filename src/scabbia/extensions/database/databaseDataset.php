<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Database;

/**
 * Database Extension: DatabaseDataset Class
 *
 * @package Scabbia
 * @subpackage Database
 * @version 1.1.0
 */
class DatabaseDataset
{
    /**
     * @ignore
     */
    public $id;
    /**
     * @ignore
     */
    public $queryString;
    /**
     * @ignore
     */
    public $parameters;
    /**
     * @ignore
     */
    public $cacheLife;
    /**
     * @ignore
     */
    public $transaction;


    /**
     * @ignore
     */
    public function __construct($uConfig)
    {
        $this->id = $uConfig['id'];
        $this->queryString = $uConfig['command'];
        $this->parameters = strlen($uConfig['parameters']) > 0 ? explode(',', $uConfig['parameters']) : array();
        $this->cacheLife = isset($uConfig['cacheLife']) ? (int)$uConfig['cacheLife'] : 0;
        $this->transaction = isset($uConfig['transaction']);
    }
}
