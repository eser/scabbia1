<?php

	namespace Scabbia\Extensions\Models;

	use Scabbia\Extensions\Datasources\datasources;
	use Scabbia\extensions;

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
			$this->db = datasources::get($uDatasource);
		}
	}

	?>