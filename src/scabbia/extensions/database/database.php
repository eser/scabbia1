<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Database;

use Scabbia\Extensions\Datasources\Datasources;

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
    const CACHE_NONE = 0;
    /**
     * @ignore
     */
    const CACHE_MEMORY = 1;
    /**
     * @ignore
     */
    const CACHE_FILE = 2;
    /**
     * @ignore
     */
    const CACHE_STORAGE = 4;

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
    public static function extensionLoad()
    {
        Datasources::registerType('pdo', 'Scabbia\\Extensions\\Database\\databaseConnection', 'Scabbia\\Extensions\\Database\\databaseProviderPdo');
        Datasources::registerType('mysql', 'Scabbia\\Extensions\\Database\\databaseConnection', 'Scabbia\\Extensions\\Database\\databaseProviderMysql');
    }
}
