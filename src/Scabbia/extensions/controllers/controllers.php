<?php

	namespace Scabbia\Extensions\Controllers;

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

			if(!isset(controllers::$models[$uDatasourceName])) {
				controllers::$models[$uDatasourceName] = datasources::get($uDatasourceName);
			}

			return controllers::$models[$uDatasourceName];
		}

		/**
		 * @ignore
		 */
		public static function load($uModelClass, $uDatasource = null) {
			if(!isset(controllers::$models[$uModelClass])) {
				controllers::$models[$uModelClass] = new $uModelClass ($uDatasource);
			}

			return controllers::$models[$uModelClass];
		}
	}

	?>