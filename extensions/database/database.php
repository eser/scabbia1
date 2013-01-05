<?php

	namespace Scabbia;

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
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string, cache
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
		public static $databases = null;
		/**
		 * @ignore
		 */
		public static $datasets = array();
		/**
		 * @ignore
		 */
		public static $default = null;
		/**
		 * @ignore
		 */
		public static $errorHandling = self::ERROR_NONE;

		/**
		 * @ignore
		 */
		public static function get($uDatabase = null) {
			if(is_null(self::$databases)) {
				self::$databases = array();

				foreach(config::get('/databaseList', array()) as $tDatabaseConfig) {
					$tDatabase = new databaseConnection($tDatabaseConfig);
					self::$databases[$tDatabase->id] = $tDatabase;

					if(is_null(self::$default) || $tDatabase->default) {
						self::$default = $tDatabase;
					}
				}

				foreach(config::get('/datasetList', array()) as $tDatasetConfig) {
					$tDataset = new databaseDataset($tDatasetConfig);
					self::$datasets[$tDataset->id] = $tDataset;
				}
			}

			if(is_null($uDatabase)) {
				return self::$default;
			}

			return self::$databases[$uDatabase];
		}
	}

?>