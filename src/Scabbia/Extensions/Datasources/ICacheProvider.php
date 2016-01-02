<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\IDataInterface;

/**
 * Datasources Extension: ICacheProvider interface
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
interface ICacheProvider extends IDataInterface
{
    public function cacheGet($uKey);
    public function cacheSet($uKey, $uObject);
    public function cacheRemove($uKey);
    public function cacheGarbageCollect();
}
