<?php

	namespace Scabbia;

	/**
	 * Controllers Extension
	 *
	 * @package Scabbia
	 * @subpackage controllers
	 * @version 1.0.2
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
		public static function loadDatabase($uDatabaseName) {
			if(!extensions::isLoaded('database')) {
				return false;
			}

			if(!isset(controllers::$models[$uDatabaseName])) {
				controllers::$models[$uDatabaseName] = database::get($uDatabaseName);
			}

			return controllers::$models[$uDatabaseName];
		}

		/**
		 * @ignore
		 */
		public static function load($uModelClass, $uDatabase = null) {
			if(!isset(controllers::$models[$uModelClass])) {
				controllers::$models[$uModelClass] = new $uModelClass ($uDatabase);
			}

			return controllers::$models[$uModelClass];
		}
	}

?>