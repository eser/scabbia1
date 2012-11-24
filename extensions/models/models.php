<?php

	/**
	 * Models Extension
	 *
	 * @package Scabbia
	 * @subpackage models
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class models {
	}

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