<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\IDataInterface;

/**
 * Datasources Extension: IServerConnection interface
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
interface IServerConnection extends IDataInterface
{
    public function connectionOpen();
    public function connectionClose();
    public function serverInfo();
}
