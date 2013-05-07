<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\IDatasource;

/**
 * Datasources Extension: IStorageProvider interface
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
interface IStorageProvider extends IDatasource
{
    public function storageGetUrl($uKey);
    public function storageGet($uKey);
    public function storagePut($uKey, $uObject);
    public function storageReplace($uKey, $uObject);
    public function storageRemove($uKey);
    public function storageGarbageCollect();
}
