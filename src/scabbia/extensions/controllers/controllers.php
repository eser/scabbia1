<?php

	namespace Scabbia\Extensions\Controllers;

	use Scabbia\Extensions\Datasources\datasources;
	use Scabbia\extensions;

	/**
	 * Controllers Extension
	 *
	 * @package Scabbia
	 * @subpackage controllers
	 * @version 1.1.0
	 *
	 * @scabbia-fwversion 1.1
	 * @scabbia-fwdepends string, io, http, views
	 * @scabbia-phpversion 5.3.0
	 * @scabbia-phpdepends
	 */
	class controllers {
		/**
		 * @ignore
		 */
		public static $models = array();
		/**
		 * @ignore
		 */
		public static $stack = array();

		/**
		 * @ignore
		 */
		public static function loadDatasource($uDatasourceName) {
			if(!isset(self::$models[$uDatasourceName])) {
				self::$models[$uDatasourceName] = datasources::get($uDatasourceName);
			}

			return self::$models[$uDatasourceName];
		}

		/**
		 * @ignore
		 */
		public static function load($uModelClass, $uDatasource = null) {
			if(!isset(self::$models[$uModelClass])) {
				self::$models[$uModelClass] = new $uModelClass ($uDatasource);
			}

			return self::$models[$uModelClass];
		}
	}

	?>