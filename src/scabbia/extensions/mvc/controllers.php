<?php

	namespace Scabbia\Extensions\Mvc;

	use Scabbia\Extensions\Datasources\datasources;
	use Scabbia\events;
	use Scabbia\extensions;

	/**
	 * Controllers Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class controllers {
		/**
		 * @ignore
		 */
		public static $controllerList = null;
		/**
		 * @ignore
		 */
		public static $models = array();
		/**
		 * @ignore
		 */
		public static $stack = array();


		/*
		 * @ignore
		 */
		public static function getControllers() {
			if(is_null(self::$controllerList)) {
				$tParms = array();
				events::invoke('registerControllers', $tParms);

				self::$controllerList = array();

				foreach(get_declared_classes() as $tClass) {
					if(!is_subclass_of($tClass, 'Scabbia\\Extensions\\Mvc\\controller')) {
						continue;
					}

					$tPos = strrpos($tClass, '\\');
					if($tPos !== false) {
						self::$controllerList[substr($tClass, $tPos + 1)] = $tClass;
						continue;
					}

					self::$controllerList[$tClass] = $tClass;
				}
			}
		}

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