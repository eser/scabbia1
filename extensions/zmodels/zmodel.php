<?php

	/**
	 * Zmodel Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class zmodel {
		/**
		 * @ignore
		 */
		public $entityName;
		/**
		 * @ignore
		 */
		public $entityDefinition;

		/**
		 * @ignore
		 */
		public function zmodel($uEntityName) {
			$this->entityName = $uEntityName;
			$this->entityDefinition = zmodels::$zmodels[$uEntityName];
		}
	}

?>