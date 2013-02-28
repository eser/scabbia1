<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

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
