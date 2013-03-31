<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Database;

use Scabbia\Extensions\Database\DatabaseDataset;
use Scabbia\Config;

/**
 * @ignore
 */
define('_AND', ' AND ');
/**
 * @ignore
 */
define('_OR', ' OR ');
/**
 * @ignore
 */
define('_IN', ' IN ');
/**
 * @ignore
 */
define('_NOTIN', ' NOT IN ');
/**
 * @ignore
 */
define('_LIKE', ' LIKE ');
/**
 * @ignore
 */
define('_NOTLIKE', ' NOT LIKE ');
/**
 * @ignore
 */
define('_ILIKE', ' ILIKE ');
/**
 * @ignore
 */
define('_NOTILIKE', ' NOT ILIKE ');

/**
 * Database Extension
 *
 * @package Scabbia
 * @subpackage Database
 * @version 1.1.0
 *
 * @todo caching for databaseQuery (get hash of given parameters)
 * @todo databaseQuery inTransaction(true)
 */
class Database
{
    /**
     * @ignore
     */
    const ERROR_NONE = 0;
    /**
     * @ignore
     */
    const ERROR_EXCEPTION = 1;


    /**
     * @ignore
     */
    public static $datasets = null;


    /**
     * @ignore
     */
    public static function getDataset($uDataset = null)
    {
        if (is_null(self::$datasets)) {
            foreach (Config::get('datasetList', array()) as $tDatasetConfig) {
                $tDataset = new DatabaseDataset($tDatasetConfig);
                self::$datasets[$tDataset->id] = $tDataset;
            }
        }

        return self::$datasets[$uDataset];
    }
}
