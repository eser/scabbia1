<?php

	namespace Scabbia\Extensions\Controllers;

	use Scabbia\extensions;
	use Scabbia\Extensions\Datasources\datasources;

	/**
	 * Controllers Extension
	 *
	 * @package Scabbia
	 * @subpackage controllers
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string, io, http, views
	 * @scabbia-phpversion 5.2.0
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
			if(!extensions::isLoaded('datasources')) {
				return false;
			}

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