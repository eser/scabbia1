<?php

	namespace Scabbia;

	/**
	 * Datasources Extension
	 *
	 * @package Scabbia
	 * @subpackage datasources
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class datasources {
		/**
		 * @ignore
		 */
		public static $datasources = null;

		/**
		 * @ignore
		 */
		public static function get($uDatasource) {
			if(is_null(self::$datasources)) {
				self::$datasources = array();

				foreach(config::get('/datasourceList', array()) as $tDatasourceConfig) {
					$tDatasource = new datasource($tDatasourceConfig);
					self::$datasources[$tDatasource->id] = $tDatasource;
				}
			}

			return self::$datasources[$uDatasource];
		}
	}

	?>