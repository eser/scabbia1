<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Database;

/**
 * Database Extension: IQueryGenerator interface
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
interface IQueryGenerator
{
    public function sqlInsert($uTable, $uObject, $uReturning = "");
    public function sqlUpdate($uTable, $uObject, $uRawObject, $uWhere, $uExtra = null);
    public function sqlDelete($uTable, $uWhere, $uExtra = null);
    public function sqlSelect($uTable, $uFields, $uRawFields, $uWhere, $uOrderBy, $uGroupBy, $uExtra = null);
}
