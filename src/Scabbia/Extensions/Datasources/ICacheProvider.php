<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\IDatasource;

/**
 * Datasources Extension: ICacheProvider interface
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
interface ICacheProvider extends IDatasource
{
    public function cacheGet($uKey);
    public function cacheSet($uKey, $uObject);
    public function cacheRemove($uKey);
    public function cacheGarbageCollect();
}