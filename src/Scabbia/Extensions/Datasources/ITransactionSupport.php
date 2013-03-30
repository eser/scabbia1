<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\IDatasource;

/**
 * Datasources Extension: ITransactionSupport interface
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
interface ITransactionSupport extends IDatasource
{
    public function beginTransaction();
    public function commit();
    public function rollBack();
}
