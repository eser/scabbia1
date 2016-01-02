<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Models;

use Scabbia\Extensions\Datasources\Datasources;

/**
 * Models Extension: Model Class
 *
 * @package Scabbia
 * @subpackage Models
 * @version 1.1.0
 */
abstract class Model
{
    /**
     * @ignore
     */
    public $db;


    /**
     * @ignore
     */
    public function __construct($uDatasource = null)
    {
        $this->db = Datasources::get($uDatasource);
    }
}
