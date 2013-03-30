<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
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
    public function sqlInsert($uTable, $uObject, $uReturning = '');
    public function sqlUpdate($uTable, $uObject, $uRawObject, $uWhere, $uExtra = null);
    public function sqlDelete($uTable, $uWhere, $uExtra = null);
    public function sqlSelect($uTable, $uFields, $uRawFields, $uWhere, $uOrderBy, $uGroupBy, $uExtra = null);
}
