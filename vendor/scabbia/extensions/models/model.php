<?php

	namespace Scabbia;

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
		public function __construct($uDatabase = null) {
			if(extensions::isLoaded('database')) {
				$this->db = database::get($uDatabase);
			}
		}
	}

?>