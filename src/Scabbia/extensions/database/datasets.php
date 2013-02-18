<?php

	namespace Scabbia\Extensions\Database;

	use Scabbia\config;
	use Scabbia\Extensions\Database\databaseDataset;

	/**
	 * Datasets Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class datasets {
		/**
		 * @ignore
		 */
		public static $datasets = null;

		/**
		 * @ignore
		 */
		public static function get($uDataset = null) {
			if(is_null(self::$datasets)) {
				foreach(config::get('/datasetList', array()) as $tDatasetConfig) {
					$tDataset = new databaseDataset($tDatasetConfig);
					self::$datasets[$tDataset->id] = $tDataset;
				}
			}

			return self::$datasets[$uDataset];
		}
	}

	?>