<?php

	namespace Scabbia\Extensions\Database;

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
	 * @subpackage database
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string, datasources, cache
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 *
	 * @todo caching for databaseQuery (get hash of given parameters)
	 * @todo databaseQuery inTransaction(true)
	 */
	class database {
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
		public static function extensionLoad() {
			datasources::registerType('pdo', 'Scabbia\\Extensions\\Database\\databaseConnection', 'Scabbia\\Extensions\\Database\\databaseProviderPdo');
			datasources::registerType('mysql', 'Scabbia\\Extensions\\Database\\databaseConnection', 'Scabbia\\Extensions\\Database\\databaseProviderMysql');
		}
	}

	?>