<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\IDataInterface;

/**
 * Datasources Extension: ITransactionSupport interface
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
interface ITransactionSupport extends IDataInterface
{
    public function beginTransaction();
    public function commit();
    public function rollBack();
}
