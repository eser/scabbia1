<?php

	define('_and', ' AND ');
	define('_or', ' OR ');
	define('_in', ' IN ');
	define('_notin', ' NOT IN ');
	define('_like', ' LIKE ');
	define('_notlike', ' NOT LIKE ');
	define('_ilike', ' ILIKE ');
	define('_notilike', ' NOT ILIKE ');

	/**
	 * Database Extension
	 *
	 * @package Scabbia
	 * @subpackage database
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string, cache
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 *
	 * @todo caching for databaseQuery (get hash of given parameters)
	 * @todo databaseQuery inTransaction(true)
	 */
	class database {
		/**
		 * @ignore
		 */
		const CACHE_NONE = 0;
		/**
		 * @ignore
		 */
		const CACHE_MEMORY = 1;
		/**
		 * @ignore
		 */
		const CACHE_FILE = 2;
		/**
		 * @ignore
		 */
		const CACHE_STORAGE = 4;

		/**
		 * @ignore
		 */
		const ERROR_NONE = 0;
		/**
		 * @ignore
		 */
		const ERROR_EXCEPTION = 1;

		/**
		 * @ignore
		 */
		public static $databases = null;
		/**
		 * @ignore
		 */
		public static $datasets = array();
		/**
		 * @ignore
		 */
		public static $default = null;
		/**
		 * @ignore
		 */
		public static $errorHandling = self::ERROR_NONE;

		/**
		 * @ignore
		 */
		public static function &get($uDatabase = null) {
			if(is_null(self::$databases)) {
				self::$databases = array();

				foreach(config::get('/databaseList', array()) as $tDatabaseConfig) {
					$tDatabase = new databaseConnection($tDatabaseConfig);
					self::$databases[$tDatabase->id] = $tDatabase;

					if(is_null(self::$default) || $tDatabase->default) {
						self::$default = $tDatabase;
					}
				}

				foreach(config::get('/datasetList', array()) as $tDatasetConfig) {
					$tDataset = new databaseDataset($tDatasetConfig);
					self::$datasets[$tDataset->id] = $tDataset;
				}
			}

			if(is_null($uDatabase)) {
				return self::$default;
			}

			return self::$databases[$uDatabase];
		}
	}

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
		public function &execute($uQuery) {
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

			$uPropsSerialized = crc32($uQuery);
			foreach($uParameters as &$tProp) {
				$uPropsSerialized .= '_' . $tProp;
			}

			if(($uCaching & database::CACHE_MEMORY) > 0 && isset($this->cache[$uPropsSerialized])) {
				$tData = & $this->cache[$uPropsSerialized]->resume($this);
				$tLoadedFromCache = true;
			}
			else {
				if(($uCaching & database::CACHE_FILE) > 0) { //  && framework::$development <= 0
					$tData = cache::fileGet($tFolder, $uPropsSerialized, -1, true);

					if($tData !== false) {
						$this->cache[$uPropsSerialized] = & $tData->resume($this);
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
							$this->cache[$uPropsSerialized] = & $tData->resume($this);
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

				foreach($uDataset->parameters as &$tParam) {
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

	/**
	 * Database Query Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class databaseQuery {
		/**
		 * @ignore
		 */
		public $database = null;

		/**
		 * @ignore
		 */
		public $table;
		/**
		 * @ignore
		 */
		public $fields;
		/**
		 * @ignore
		 */
		public $parameters;
		/**
		 * @ignore
		 */
		public $where;
		/**
		 * @ignore
		 */
		public $groupby;
		/**
		 * @ignore
		 */
		public $orderby;
		/**
		 * @ignore
		 */
		public $limit;
		/**
		 * @ignore
		 */
		public $offset;
		/**
		 * @ignore
		 */
		public $sequence;
		/**
		 * @ignore
		 */
		public $caching;
		/**
		 * @ignore
		 */
		public $debug;

		/**
		 * @ignore
		 */
		public function __construct(&$uDatabase = null) {
			$this->setDatabase($uDatabase);
		}

		/**
		 * @ignore
		 */
		public function setDatabase(&$uDatabase = null) {
			if(!is_null($uDatabase)) {
				$this->database = & $uDatabase;
			}
			else {
				$this->database = database::get(); // default
			}

			$this->clear();
		}

		/**
		 * @ignore
		 */
		public function setDatabaseName($uDatabaseName) {
			$this->database = database::get($uDatabaseName);
			$this->clear();
		}

		/**
		 * @ignore
		 */
		public function clear() {
			$this->table = '';
			$this->fields = array();
			$this->parameters = array();
			$this->where = '';
			$this->groupby = '';
			$this->orderby = '';
			$this->limit = -1;
			$this->offset = -1;
			$this->sequence = '';
			$this->returning = '';
			$this->caching = database::CACHE_NONE;
			$this->debug = false;
		}

		/**
		 * @ignore
		 */
		public function &setTable($uTableName) {
			$this->table = $uTableName;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &joinTable($uTableName, $uCondition, $uJoinType = 'INNER') {
			$this->table .= ' ' . $uJoinType . ' JOIN ' . $uTableName . ' ON ' . $uCondition;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &setFields($uArray) {
			foreach($uArray as $tField => &$tValue) {
				// $this->fields[$tField] = string::squote($tValue, true);
				if(is_null($tValue)) {
					$this->fields[$tField] = 'NULL';
				}
				else {
					$this->fields[$tField] = ':' . $tField;
					$this->parameters[$this->fields[$tField]] = $tValue;
				}
			}

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &setFieldsDirect($uArray) {
			$this->fields = & $uArray;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &addField($uField, $uValue = null) {
			if(func_num_args() == 1) {
				$this->fields[] = $uField;

				return $this;
			}

			if(is_null($uValue)) {
				$this->fields[$uField] = 'NULL';
			}
			else {
				// $this->fields[$uField] = string::squote($uValue, true);
				$this->fields[$uField] = ':' . $uField;
				$this->parameters[$this->fields[$uField]] = $uValue;
			}

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &addFieldDirect($uField, $uValue) {
			$this->fields[$uField] = $uValue;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &addParameter($uParameter, $uValue) {
			$this->parameters[$uParameter] = $uValue;

			return $this;
		}

		/**
		 * @ignore
		 */
		private static function constructWhere($uArray, $uIsList = false) {
			$tOutput = '(';
			$tPreviousElement = null;

			foreach($uArray as &$tElement) {
				if(is_array($tElement)) {
					$tOutput .= self::constructWhere($tElement, ($tPreviousElement == _in || $tPreviousElement == _notin));
					continue;
				}

				if($uIsList) {
					if(!is_null($tPreviousElement)) {
						$tOutput .= ', ' . string::squote($tElement, true);
					}
					else {
						$tOutput .= string::squote($tElement, true);
					}
				}
				else {
					$tOutput .= $tElement;
				}

				$tPreviousElement = $tElement;
			}

			$tOutput .= ')';

			return $tOutput;
		}

		/**
		 * @ignore
		 */
		public function &setWhere($uCondition, $uList = null) {
			if(is_array($uCondition)) {
				$this->where = self::constructWhere($uCondition);

				return $this;
			}

			$this->where = $uCondition;

			if(!is_null($uList)) {
				$this->where .= ' (' . implode(', ', string::squoteArray($uList, true)) . ')';
			}

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &andWhere($uCondition, $uList = null, $uKeyword = 'OR') {
			if(is_array($uCondition)) {
				if(count($uCondition) > 0) {
					if(strlen($this->where) > 0) {
						$this->where .= ' AND ';
					}

					$this->where .= '(' . implode(' ' . $uKeyword . ' ', $uCondition) . ')';
				}
			}
			else {
				if(strlen($this->where) > 0) {
					$this->where .= ' AND ';
				}

				$this->where .= $uCondition;

				if(!is_null($uList)) {
					$this->where .= ' (' . implode(', ', string::squoteArray($uList, true)) . ')';
				}
			}

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &orWhere($uCondition, $uList = null, $uKeyword = 'AND') {
			if(is_array($uCondition)) {
				if(count($uCondition) > 0) {
					if(strlen($this->where) > 0) {
						$this->where .= ' OR ';
					}

					$this->where .= '(' . implode(' ' . $uKeyword . ' ', $uCondition) . ')';
				}
			}
			else {
				if(strlen($this->where) > 0) {
					$this->where .= ' OR ';
				}

				$this->where .= $uCondition;

				if(!is_null($uList)) {
					$this->where .= ' (' . implode(', ', string::squoteArray($uList, true)) . ')';
				}
			}

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &setGroupBy($uGroupBy) {
			$this->groupby = $uGroupBy;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &addGroupBy($uGroupBy) {
			$this->groupby .= ', ' . $uGroupBy;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &setOrderBy($uOrderBy, $uOrder = null) {
			$this->orderby = $uOrderBy;
			if(!is_null($uOrder)) {
				$this->orderby .= ' ' . $uOrder;
			}

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &addOrderBy($uOrderBy, $uOrder = null) {
			$this->orderby .= ', ' . $uOrderBy;
			if(!is_null($uOrder)) {
				$this->orderby .= ' ' . $uOrder;
			}

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &setLimit($uLimit) {
			$this->limit = $uLimit;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &setOffset($uOffset) {
			$this->offset = $uOffset;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &setSequence($uSequence) {
			$this->sequence = $uSequence;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &setReturning($uReturning) {
			$this->returning = $uReturning;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &setCaching($uCaching) {
			$this->caching = $uCaching;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function &setDebug($uDebug) {
			$this->debug = $uDebug;

			return $this;
		}
		/**
		 * @ignore
		 */
		public function &insert() {
			$tQuery = $this->database->provider->sqlInsert($this->table, $this->fields, $this->returning);
			if($this->debug) {
				echo 'Insert Query: ', $tQuery;
			}
			$tReturn = $this->database->query(
				$tQuery,
				$this->parameters,
				$this->caching
			);

			if(!is_null($this->sequence) && strlen($this->sequence) > 0) {
				$tReturn->_lastInsertId = $this->database->lastInsertId($this->sequence);
			}
			else {
				$tReturn->_lastInsertId = $this->database->lastInsertId();
			}

			$this->clear();

			return $tReturn;
		}

		/**
		 * @ignore
		 */
		public function &update() {
			$tQuery = $this->database->provider->sqlUpdate($this->table, $this->fields, $this->where, array('limit' => $this->limit));
			if($this->debug) {
				echo 'Update Query: ', $tQuery;
			}
			$tReturn = $this->database->query(
				$tQuery,
				$this->parameters,
				$this->caching
			);

			$this->clear();

			return $tReturn;
		}

		/**
		 * @ignore
		 */
		public function &delete() {
			$tQuery = $this->database->provider->sqlDelete($this->table, $this->where, array('limit' => $this->limit));
			if($this->debug) {
				echo 'Delete Query: ', $tQuery;
			}
			$tReturn = $this->database->query(
				$tQuery,
				$this->parameters,
				$this->caching
			);

			$this->clear();

			return $tReturn;
		}

		/**
		 * @ignore
		 */
		public function &get() {
			$tQuery = $this->database->provider->sqlSelect($this->table, $this->fields, $this->where, $this->orderby, $this->groupby, array('limit' => $this->limit, 'offset' => $this->offset));
			if($this->debug) {
				echo 'Get Query: ', $tQuery;
			}
			$tReturn = $this->database->query(
				$tQuery,
				$this->parameters,
				$this->caching
			);

			$this->clear();

			return $tReturn;
		}

		/**
		 * @ignore
		 */
		public function &calculate($uTable, $uOperation = 'COUNT', $uField = '*', $uWhere = null) {
			$tQuery = $this->database->provider->sqlSelect($uTable, array($uOperation . '(' . $uField . ')'), $uWhere, null, null);
			if($this->debug) {
				echo 'Calculate Query: ', $tQuery;
			}
			$tReturn = $this->database->query(
				$tQuery,
				array(),
				$this->caching
			);

			return $tReturn;
		}

		/**
		 * @ignore
		 */
		public function runSmartObject(&$uInstance) {
			$tRow = $this->getRow();

			foreach($tRow as $tKey => $tValue) {
				$uInstance->obtained[$tKey] = $tValue;
			}
		}
	}

	/**
	 * Database Query Result Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class databaseQueryResult implements ArrayAccess, Countable, Iterator {
		/**
		 * @ignore
		 */
		public $_query;
		/**
		 * @ignore
		 */
		public $_parameters;
		/**
		 * @ignore
		 */
		public $_object = null;
		/**
		 * @ignore
		 */
		public $_database = null;
		/**
		 * @ignore
		 */
		public $_caching = null;
		/**
		 * @ignore
		 */
		public $_directory = null;
		/**
		 * @ignore
		 */
		public $_filename = null;
		/**
		 * @ignore
		 */
		public $_rows = array();
		/**
		 * @ignore
		 */
		public $_count = -1;
		/**
		 * @ignore
		 */
		public $_cursor = 0;
		/**
		 * @ignore
		 */
		public $_lastInsertId = null;

		/**
		 * @ignore
		 */
		public function __construct($uQuery, $uParameters, &$uDatabase, $uCaching, $uDirectory, $uFilename) {
			$this->_query = & $uQuery;
			$this->_parameters = & $uParameters;
			$this->_database = & $uDatabase;
			$this->_caching = & $uCaching;
			$this->_directory = & $uDirectory;
			$this->_filename = & $uFilename;
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
		public function __isset($uKey) {
			return isset($this->_rows[$this->_cursor][$uKey]);
		}

		/**
		 * @ignore
		 */
		public function __get($uKey) {
			return $this->_rows[$this->_cursor][$uKey];
		}

		/**
		 * @ignore
		 */
		public function __set($uKey, $uValue) {
			return $this->_rows[$this->_cursor][$uKey] = $uValue;
		}

		/**
		 * @ignore
		 */
		public function __unset($uKey) {
			unset($this->_rows[$this->_cursor][$uKey]);
		}

		/**
		 * @ignore
		 */
		public function offsetExists($uOffset) {
			return isset($this->_rows[$this->_cursor][$uOffset]);
		}

		/**
		 * @ignore
		 */
		public function offsetGet($uOffset) {
			return $this->_rows[$this->_cursor][$uOffset];
		}

		/**
		 * @ignore
		 */
		public function offsetSet($uOffset, $uValue) {
			return $this->_rows[$this->_cursor][$uOffset] = $uValue;
		}

		/**
		 * @ignore
		 */
		public function offsetUnset($uOffset) {
			unset($this->_rows[$this->_cursor][$uOffset]);
		}

		/**
		 * @ignore
		 */
		public function count() {
			return $this->_count;
		}

		/**
		 * @ignore
		 */
		public function current() {
			return $this->_rows[$this->_cursor];
		}

		/**
		 * @ignore
		 */
		public function key() {
			return $this->_cursor;
		}

		/**
		 * @ignore
		 */
		public function next() {
			++$this->_cursor;
		}

		/**
		 * @ignore
		 */
		public function execute() {
			try {
				$this->_object = $this->_database->provider->queryDirect($this->_query, $this->_parameters);
				$this->_count = $this->_database->provider->itCount($this->_object);
			}
			catch(Exception $ex) {
				if(database::$errorHandling == database::ERROR_EXCEPTION) {
					throw $ex;
				}

				$this->close();

				return false;
			}

			$this->close();
			
			return $this->_count;
		}

		/**
		 * @ignore
		 */
		public function all() {
			// $this->_cursor = 0;
			while($this->valid()) {
				++$this->_cursor;
			}

			$this->close();

			return $this->_rows;
		}

		/**
		 * @ignore
		 */
		public function column($uKey) {
			$tItems = array();

			$this->_cursor = 0;
			while($this->valid()) {
				$tCurrent = $this->current();
				$tItems[] = $tCurrent[$uKey];
				++$this->_cursor;
			}

			$this->close();

			return $tItems;
		}

		/**
		 * @ignore
		 */
		public function row() {
			if(!$this->valid()) {
				$this->close();

				return false;
			}

			$tRow = $this->current();
			$this->close();

			return $tRow;
		}

		/**
		 * @ignore
		 */
		public function scalar($uColumn = 0, $uDefault = false) {
			if(!$this->valid()) {
				$this->close();

				return $uDefault;
			}

			$tRow = $this->current();
			$this->close();

			for($i = 0; $i < $uColumn; $i++) {
				next($tRow);
			}

			return current($tRow);
		}

		/**
		 * @ignore
		 */
		public function rewind() {
			$this->_cursor = 0;
		}

		/**
		 * @ignore
		 */
		public function valid() {
			if(count($this->_rows) > $this->_cursor) {
				return true;
			}

			if(is_null($this->_object)) {
				try {
					$this->_object = $this->_database->provider->queryDirect($this->_query, $this->_parameters);
					$this->_count = $this->_database->provider->itCount($this->_object);

					if($this->_count <= $this->_cursor) {
						return false;
					}

					$this->_rows[$this->_cursor] = $this->_database->provider->itSeek($this->_object, $this->_cursor);
				}
				catch(Exception $ex) {
					if(database::$errorHandling == database::ERROR_EXCEPTION) {
						throw $ex;
					}

					return false;
				}

				return true;
			}

			if($this->_count <= $this->_cursor) {
				return false;
			}

			$this->_rows[$this->_cursor] = $this->_database->provider->itNext($this->_object);

			return true;
		}

		/**
		 * @ignore
		 */
		public function close() {
			if(!is_null($this->_object)) {
				$this->_database->provider->itClose($this->_object);
				$this->_object = null;
			}

			$this->_cursor = 0;

			if(($this->_caching & database::CACHE_MEMORY) > 0) {
				$this->_database->cache[$this->_filename] = & $this;
			}

			$this->_database = null;

			if(($this->_caching & database::CACHE_FILE) > 0) {
				cache::fileSet($this->_directory, $this->_filename, $this);
			}
			else {
				if(($this->_caching & database::CACHE_STORAGE) > 0) {
					$tKey = strtr($this->_directory, '/', '_') . $this->_filename;
					cache::storageSet($tKey, $this);
				}
			}
		}

		/**
		 * @ignore
		 */
		public function &resume($uDatabase) {
			$this->_database = & $uDatabase;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function lastInsertId() {
			return $this->_lastInsertId;
		}
	}

?>