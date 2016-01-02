<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\IDataInterface;

/**
 * Datasources Extension: IStorageProvider interface
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
interface IStorageProvider extends IDataInterface
{
    public function storageGetUrl($uKey);
    public function storageGet($uKey);
    public function storagePut($uKey, $uObject);
    public function storageReplace($uKey, $uObject);
    public function storageRemove($uKey);
    public function storageGarbageCollect();
}
