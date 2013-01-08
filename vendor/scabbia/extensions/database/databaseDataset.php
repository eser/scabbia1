<?php

	namespace Scabbia;

	/**
	 * Database Dataset Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class databaseDataset {
		/**
		 * @ignore
		 */
		public $id;
		/**
		 * @ignore
		 */
		public $queryString;
		/**
		 * @ignore
		 */
		public $parameters;
		/**
		 * @ignore
		 */
		public $cacheLife;
		/**
		 * @ignore
		 */
		public $transaction;

		/**
		 * @ignore
		 */
		public function __construct($uConfig) {
			$this->id = $uConfig['id'];
			$this->queryString = $uConfig['command'];
			$this->parameters = strlen($uConfig['parameters']) > 0 ? explode(',', $uConfig['parameters']) : array();
			$this->cacheLife = isset($uConfig['cacheLife']) ? (int)$uConfig['cacheLife'] : 0;
			$this->transaction = isset($uConfig['transaction']);
		}
	}

	?>