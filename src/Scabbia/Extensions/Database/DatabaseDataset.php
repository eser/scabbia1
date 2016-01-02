<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
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
    public $cacheTtl;
    /**
     * @ignore
     */
    public $transaction;


    /**
     * @ignore
     */
    public function __construct(array $uConfig)
    {
        $this->id = $uConfig['id'];
        $this->queryString = $uConfig['command'];
        $this->parameters = strlen($uConfig['parameters']) > 0 ? explode(',', $uConfig['parameters']) : array();
        $this->cacheTtl = isset($uConfig['cacheTtl']) ? $uConfig['cacheTtl'] : 0;
        $this->transaction = isset($uConfig['transaction']);
    }
}
