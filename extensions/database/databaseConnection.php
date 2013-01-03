<?php

	/**
	 * Database Connection Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class databaseConnection {
		/**
		 * @ignore
		 */
		public $id;
		/**
		 * @ignore
		 */
		public $default;
		/**
		 * @ignore
		 */
		public $provider;
		/**
		 * @ignore
		 */
		public $cache = array();
		/**
		 * @ignore
		 */
		public $stats = array('cache' => 0, 'query' => 0);
		/**
		 * @ignore
		 */
		public $inTransaction = false;
		/**
		 * @ignore
		 */
		public $initCommand;

		/**
		 * @ignore
		 */
		public function __construct($uConfig) {
			$this->id = $uConfig['id'];
			$this->default = isset($uConfig['default']);

			$tProvider = 'databaseprovider_' . (isset($uConfig['provider']) ? $uConfig['provider'] : 'pdo');

			$this->provider = new $tProvider ($uConfig);

			if(isset($uConfig['initCommand'])) {
				$this->initCommand = $uConfig['initCommand'];
			}
		}

		/**
		 * @ignore
		 */
		public function __destruct() {
			$this->close();
		}

		/**
		 * @ignore
		 */
		public function open() {
			$this->provider->open();

			if(strlen($this->initCommand) > 0) {
				// $this->execute($this->initCommand); // occurs recursive loop
				//! may need pass the initial command to the profiler extension
				try {
					$this->provider->execute($this->initCommand);
				}
				catch(Exception $ex) {
					if(database::$errorHandling == database::ERROR_EXCEPTION) {
						throw $ex;
					}

					return false;
				}
			}
		}

		/**
		 * @ignore
		 */
		public function close() {
			$this->provider->close();
			$this->provider = null;
		}

		/**
		 * @ignore
		 */
		public function beginTransaction() {
			$this->open();
			$this->provider->beginTransaction();
			$this->inTransaction = true;
		}

		/**
		 * @ignore
		 */
		public function commit() {
			$this->provider->commit();
			$this->inTransaction = false;
		}

		/**
		 * @ignore
		 */
		public function rollBack() {
			$this->provider->rollBack();
			$this->inTransaction = false;
		}

		/**
		 * @ignore
		 */
		public function execute($uQuery) {
			$this->open();

			if(extensions::isLoaded('profiler')) {
				profiler::start(
					'databaseQuery',
					array(
					     'query' => $uQuery,
					     'parameters' => null
					)
				);
			}

			try {
				$tReturn = $this->provider->execute($uQuery);
			}
			catch(Exception $ex) {
				if(database::$errorHandling == database::ERROR_EXCEPTION) {
					throw $ex;
				}

				$tReturn = false;
			}

			if(extensions::isLoaded('profiler')) {
				profiler::stop();
			}

			return $tReturn;
		}

		/**
		 * @ignore
		 */
		public function query($uQuery, $uParameters = array(), $uCaching = database::CACHE_MEMORY) {
			$this->open();

			if(extensions::isLoaded('profiler')) {
				profiler::start(
					'databaseQuery',
					array(
					     'query' => $uQuery,
					     'parameters' => $uParameters
					)
				);
			}

			$tFolder = 'database/' . $this->id . '/';

			$uPropsSerialized = hash('adler32', $uQuery);
			foreach($uParameters as $tProp) {
				$uPropsSerialized .= '_' . $tProp;
			}

			if(($uCaching & database::CACHE_MEMORY) > 0 && isset($this->cache[$uPropsSerialized])) {
				$tData = $this->cache[$uPropsSerialized]->resume($this);
				$tLoadedFromCache = true;
			}
			else {
				if(($uCaching & database::CACHE_FILE) > 0) { //  && framework::$development <= 0
					$tData = cache::fileGet($tFolder, $uPropsSerialized, -1, true);

					if($tData !== false) {
						$this->cache[$uPropsSerialized] = $tData->resume($this);
						$tLoadedFromCache = true;
					}
					else {
						$tLoadedFromCache = false;
					}
				}
				else {
					if(($uCaching & database::CACHE_STORAGE) > 0) { //  && framework::$development <= 0
						$tKey = strtr($tFolder, '/', '_') . $uPropsSerialized;
						$tData = cache::storageGet($tKey);

						if($tData !== false) {
							$this->cache[$uPropsSerialized] = $tData->resume($this);
							$tLoadedFromCache = true;
						}
						else {
							$tLoadedFromCache = false;
						}
					}
					else {
						$tData = false;
						$tLoadedFromCache = false;
					}
				}
			}

			if($tData === false) {
				$tData = new databaseQueryResult($uQuery, $uParameters, $this, $uCaching, $tFolder, $uPropsSerialized);
				++$this->stats['query'];
			}
			else {
				++$this->stats['cache'];
			}

			if(extensions::isLoaded('profiler')) {
				profiler::stop(
				//! affected rows
					array(
					     'affectedRows' => $tData->count(),
					     'fromCache' => $tLoadedFromCache
					)
				);
			}

			return $tData;
		}

		/**
		 * @ignore
		 */
		public function lastInsertId($uName = null) {
			return $this->provider->lastInsertId($uName);
		}

		/**
		 * @ignore
		 */
		public function serverInfo() {
			$this->open();

			return $this->provider->serverInfo();
		}

		/**
		 * @ignore
		 */
		public function dataset() {
			$this->open();

			$uProps = func_get_args();
			$uDataset = database::$datasets[array_shift($uProps)];

			if($uDataset->transaction) {
				$this->beginTransaction();
			}

			try {
				$tCount = 0;
				$tArray = array();

				foreach($uDataset->parameters as $tParam) {
					$tArray[$tParam] = $uProps[$tCount++];
				}

				try {
					$tResult = $this->query($uDataset->queryString, $tArray, true); //! constant
				}
				catch(Exception $ex) {
					if(database::$errorHandling == database::ERROR_EXCEPTION) {
						throw $ex;
					}

					$tReturn = false;
				}

				if($this->inTransaction) {
					$this->commit();
				}
			}
			catch(Exception $ex) {
				if($this->inTransaction) {
					$this->rollBack();
				}

				throw $ex;
			}

			++$this->stats['query'];

			if(isset($tResult)) {
				return $tResult;
			}

			return false;
		}

		/**
		 * @ignore
		 */
		public function createQuery() {
			return new databaseQuery($this);
		}
	}

?>