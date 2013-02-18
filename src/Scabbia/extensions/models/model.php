<?php

	namespace Scabbia\Extensions\Models;

	/**
	 * Model Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	abstract class model {
		/**
		 * @ignore
		 */
		public $db;

		/**
		 * @ignore
		 */
		public function __construct($uDatasource = null) {
			if(extensions::isLoaded('datasources')) {
				$this->db = datasources::get($uDatasource);
			}
		}
	}

	?>