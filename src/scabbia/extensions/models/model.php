<?php

namespace Scabbia\Extensions\Models;

use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions;

/**
 * Model Class
 *
 * @package Scabbia
 * @subpackage LayerExtensions
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
