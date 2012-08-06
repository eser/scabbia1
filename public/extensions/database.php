<?php

if(extensions::isSelected('database')) {
	/**
	* Database Extension
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*
	* @todo integrate with cache extension
	* @todo caching for databaseQuery (get hash of given parameters)
	*/
	class database {
		/**
		* @ignore
		*/
		public static $databases = array();
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
		public static function extension_info() {
			return array(
				'name' => 'database',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array('pdo'),
				'fwversion' => '1.0',
				'fwdepends' => array('string', 'cache', 'profiler')
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
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

		/**
		* @ignore
		*/
		public static function &get() {
			$uArgs = func_get_args();

			switch(count($uArgs)) {
			case 0:
				return self::$default;
				break;
			case 1:
				return self::$databases[$uArgs[0]];
				break;
			}

			return null;
		}

		/**
		* @ignore
		*/
		public static function sqlInsert($uTable, $uObject, $uReturning = '') {
			$tSql =
				'INSERT INTO ' . $uTable . ' ('
				. implode(', ', array_keys($uObject))
				. ') VALUES ('
				. implode(', ', array_values($uObject))
				. ')';

			if(strlen($uReturning) > 0) {
				$tSql .= ' RETURNING ' . $uReturning;
			}

			return $tSql;
		}

		/**
		* @ignore
		*/
		public static function sqlUpdate($uTable, $uObject, $uWhere, $uExtra = '') {
			$tPairs = array();
			foreach($uObject as $tKey => &$tValue) {
				$tPairs[] = $tKey . '=' . $tValue;
			}

			$tSql = 'UPDATE ' . $uTable . ' SET '
				. implode(', ', $tPairs);

			if(strlen($uWhere) > 0) {
				$tSql .= ' WHERE ' . $uWhere;
			}

			if(strlen($uExtra) > 0) {
				$tSql .= ' ' . $uExtra;
			}

			return $tSql;
		}

		/**
		* @ignore
		*/
		public static function sqlDelete($uTable, $uWhere, $uExtra = '') {
			$tSql = 'DELETE FROM ' . $uTable;

			if(strlen($uWhere) > 0) {
				$tSql .= ' WHERE ' . $uWhere;
			}

			if(strlen($uExtra) > 0) {
				$tSql .= ' ' . $uExtra;
			}

			return $tSql;
		}

		/**
		* @ignore
		*/
		public static function sqlSelect($uTable, $uFields, $uWhere, $uOrderBy, $uExtra = '') {
			$tSql = 'SELECT ';

			if(count($uFields) > 0) {
				$tSql .= implode(', ', $uFields);
			}
			else {
				$tSql .= '*';
			}

			$tSql .= ' FROM ' . $uTable;

			if(strlen($uWhere) > 0) {
				$tSql .= ' WHERE ' . $uWhere;
			}

			if(!is_null($uOrderBy) && strlen($uOrderBy) > 0) {
				$tSql .= ' ORDER BY ' . $uOrderBy;
			}

			if(strlen($uExtra) > 0) {
				$tSql .= ' ' . $uExtra;
			}

			return $tSql;
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
		protected $connection = null;
		/**
		* @ignore
		*/
		public $driver = null;
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
		public $active = false;
		/**
		* @ignore
		*/
		public $inTransaction = false;
		/**
		* @ignore
		*/
		protected $pdoString;
		/**
		* @ignore
		*/
		protected $username;
		/**
		* @ignore
		*/
		protected $password;
		/**
		* @ignore
		*/
		protected $initCommand;
		/**
		* @ignore
		*/
		protected $overrideCase;
		/**
		* @ignore
		*/
		protected $persistent;
		/**
		* @ignore
		*/
		public $affectedRows;

		/**
		* @ignore
		*/
		public function __construct($uConfig) {
			$this->id = $uConfig['@id'];
			$this->default = isset($uConfig['@default']);
			$this->pdoString = $uConfig['pdoString']['.'];
			$this->username = $uConfig['username']['.'];
			$this->password = $uConfig['password']['.'];

			if(isset($uConfig['initCommand']) && array_key_exists('.', $uConfig['initCommand'])) {
				$this->initCommand = $uConfig['initCommand']['.'];
			}

			if(isset($uConfig['overrideCase'])) {
				$this->overrideCase = $uConfig['overrideCase']['.'];
			}

			$this->persistent = isset($uConfig['persistent']);
		}

		/**
		* @ignore
		*/
		public function __destruct() {
			if($this->active) {
				$this->close();
			}
		}

		/**
		* @ignore
		*/
		public function open() {
			$tParms = array();
			if($this->persistent) {
				$tParms[PDO::ATTR_PERSISTENT] = true;
			}

			switch($this->overrideCase) {
			case 'lower':
				$tParms[PDO::ATTR_CASE] = PDO::CASE_LOWER;
				break;
			case 'upper':
				$tParms[PDO::ATTR_CASE] = PDO::CASE_UPPER;
				break;
			default:
				$tParms[PDO::ATTR_CASE] = PDO::CASE_NATURAL;
				break;
			}

			$tParms[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;

			try {
				$this->connection = new PDO($this->pdoString, $this->username, $this->password, $tParms);
			}
			catch(PDOException $ex) {
				throw new PDOException('PDO Exception: ' . $ex->getMessage());
			}

			$this->driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
			$this->active = true;

			if(strlen($this->initCommand) > 0) {
				$this->connection->exec($this->initCommand);
			}
		}

		/**
		* @ignore
		*/
		public function close() {
			$this->active = false;
		}

		/**
		* @ignore
		*/
		public function beginTransaction() {
			$this->open();
			$this->connection->beginTransaction();
			$this->inTransaction = true;
		}

		/**
		* @ignore
		*/
		public function commit() {
			$this->connection->commit();
			$this->inTransaction = false;
		}

		/**
		* @ignore
		*/
		public function rollBack() {
			$this->connection->rollBack();
			$this->inTransaction = false;
		}

		/**
		* @ignore
		*/
		public function query($uQuery, $uParameters = array()) {
			$this->open();

			profiler::start(
				'databaseQuery',
				array(
					'query' => $uQuery,
					'parameters' => $uParameters
				)
			);

			$tQuery = $this->connection->prepare($uQuery);
			$tResult = $tQuery->execute($uParameters);
			// $tQuery->closeCursor();
			$this->affectedRows = $tQuery->rowCount();

			profiler::stop();

			if($tResult) {
				return $this->affectedRows;
			}

			return false;
		}

		/**
		* @ignore
		*/
		public function &queryFetch($uQuery, $uParameters = array()) {
			$this->open();

			profiler::start(
				'databaseQuery',
				array(
					'query' => $uQuery,
					'parameters' => $uParameters
				)
			);

			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);

			profiler::stop();

			$tIterator = new dataRowsIterator($tQuery);
			return $tIterator;
		}

		/**
		* @ignore
		*/
		public function &querySet($uQuery, $uParameters = array()) {
			$this->open();

			profiler::start(
				'databaseQuery',
				array(
					'query' => $uQuery,
					'parameters' => $uParameters
				)
			);

			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);
			$tResult = $tQuery->fetchAll(PDO::FETCH_ASSOC);
			// $this->affectedRows = $tQuery->rowCount();
			$tQuery->closeCursor();

			profiler::stop();

			return $tResult;
		}

		/**
		* @ignore
		*/
		public function &queryRow($uQuery, $uParameters = array()) {
			$this->open();

			profiler::start(
				'databaseQuery',
				array(
					'query' => $uQuery,
					'parameters' => $uParameters
				)
			);

			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);
			$tResult = $tQuery->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
			// $this->affectedRows = $tQuery->rowCount();
			$tQuery->closeCursor();

			profiler::stop();

			return $tResult;
		}

		/**
		* @ignore
		*/
		public function &queryScalar($uQuery, $uParameters = array()) {
			$this->open();

			profiler::start(
				'databaseQuery',
				array(
					'query' => $uQuery,
					'parameters' => $uParameters
				)
			);

			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);
			$tResult = $tQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
			// $this->affectedRows = $tQuery->rowCount();
			$tQuery->closeCursor();

			profiler::stop();

			return $tResult[0];
		}

		/**
		* @ignore
		*/
		public function lastInsertId($uName = null) {
			return $this->connection->lastInsertId($uName);
		}

		/**
		* @ignore
		*/
		public function affectedRows() {
			return $this->affectedRows;
		}

		/**
		* @ignore
		*/
		public function serverInfo() {
			return $this->connection->getAttribute(PDO::ATTR_SERVER_INFO);
		}

		/**
		* @ignore
		*/
		public function dataset() {
			$uProps = func_get_args();
			$uDataset = array_shift($uProps);

			return $this->datasetInternal(database::$datasets[$uDataset], $uProps);
		}

		/**
		* @ignore
		*/
		public function &datasetFetch() {
			$uProps = func_get_args();
			$uDataset = array_shift($uProps);

			return $this->datasetFetchInternal(database::$datasets[$uDataset], $uProps);
		}

		/**
		* @ignore
		*/
		public function datasetSet() {
			$uProps = func_get_args();
			$uDataset = array_shift($uProps);
			$tData = $this->datasetSetInternal(database::$datasets[$uDataset], $uProps);

			return $tData['data'];
		}

		/**
		* @ignore
		*/
		public function datasetRow() {
			$uProps = func_get_args();
			$uDataset = array_shift($uProps);
			$tData = $this->datasetSetInternal(database::$datasets[$uDataset], $uProps);

			if(count($tData['data']) > 0) {
				return $tData['data'][0];
			}

			return null;
		}

		/**
		* @ignore
		*/
		public function datasetScalar() {
			$uProps = func_get_args();
			$uDataset = array_shift($uProps);

			$tData = $this->datasetSetInternal(database::$datasets[$uDataset], $uProps);

			if(count($tData['data']) > 0) {
				return current($tData['data'][0]);
			}

			return null;
		}

		/**
		* @ignore
		*/
		public function datasetInternal(&$uDataset, &$uProps) {
//			if(count($uProps) == 1 && is_array($uProps[0])) {
//				$tPropMaps = array();
//
//				foreach($uDataset->parameters as $tKey => &$tParam) {
//					if(isset($uProps[0][$tParam])) {
//						$tPropMaps[] = $uProps[0][$tParam];
//						continue;
//					}
//
//					$tPropMaps[] = null;
//				}
//
//				$uProps = &$tPropMaps;
//			}

			if($uDataset->transaction) {
				$this->beginTransaction();
			}

			try {
				$tCount = 0;
				$tArray = array();

				foreach($uDataset->parameters as &$tParam) {
					$tArray[$tParam] = $uProps[$tCount++];
				}

				$tQueryExecute = string::format($uDataset->queryString, $tArray);
				$tResult = $this->query($tQueryExecute);

				if($this->inTransaction) {
					$this->commit();
				}
			}
			catch(PDOException $ex) {
				if($this->inTransaction) {
					$this->rollBack();
				}

				throw new PDOException($ex->getMessage());
			}

			$this->stats['query']++;

			if(isset($tResult)) {
				return $this->affectedRows();
			}

			return false;
		}

		/**
		* @ignore
		*/
		public function &datasetFetchInternal(&$uDataset, &$uProps) {
//			if(count($uProps) == 1 && is_array($uProps[0])) {
//				$tPropMaps = array();
//
//				foreach($this->parameters as $tKey => &$tParam) {
//					if(isset($uProps[0][$tParam])) {
//						$tPropMaps[] = $uProps[0][$tParam];
//						continue;
//					}
//
//					$tPropMaps[] = null;
//				}
//
//				$uProps = &$tPropMaps;
//			}

			if($uDataset->transaction) {
				$this->beginTransaction();
			}

			try {
				$tCount = 0;
				$tArray = array();

				foreach($uDataset->parameters as &$tParam) {
					$tArray[$tParam] = $uProps[$tCount++];
				}

				$tQueryExecute = string::format($uDataset->queryString, $tArray);
				$tResult = $this->queryFetch($tQueryExecute);

				if($this->inTransaction) {
					$this->commit();
				}
			}
			catch(PDOException $ex) {
				if($this->inTransaction) {
					$this->rollBack();
				}

				throw new PDOException($ex->getMessage());
			}

			$this->stats['query']++;

			if(isset($tResult)) {
				return $tResult;
			}

			return false;
		}

		/**
		* @ignore
		*/
		public function &datasetSetInternal(&$uDataset, &$uProps) {
//			if(count($uProps) == 1 && is_array($uProps[0])) {
//				$tPropMaps = array();
//
//				foreach($this->parameters as $tKey => &$tParam) {
//					if(isset($uProps[0][$tParam])) {
//						$tPropMaps[] = $uProps[0][$tParam];
//						continue;
//					}
//
//					$tPropMaps[] = null;
//				}
//
//				$uProps = &$tPropMaps;
//			}

			$tFolder = 'database/' . $this->id . '/';

			$uPropsSerialized = $uDataset->id;
			foreach($uProps as &$tProp) {
				$uPropsSerialized .= '_' . $tProp;
			}

			if(isset($this->cache[$uPropsSerialized])) {
				$tData = &$this->cache[$uPropsSerialized];
				$tData['data']->iterator->rewind(); // rewind ArrayIterator
				$tLoadedFromCache = true;
			}
			else if($uDataset->cacheLife > 0) {
				$tData = cache::get($tFolder, $uPropsSerialized, $uDataset->cacheLife);
				if($tData !== false) {
					$tLoadedFromCache = true;

					$this->cache[$uPropsSerialized] = &$tData;
				}
				else {
					$tLoadedFromCache = false;
				}
			}
			else {
				$tData = false;
				$tLoadedFromCache = false;
			}

			if($tData === false) {
				if($uDataset->transaction) {
					$this->beginTransaction();
				}

				try {
					$tCount = 0;
					$tArray = array();

					foreach($uDataset->parameters as &$tParam) {
						$tArray[$tParam] = $uProps[$tCount++];
					}

					$tQueryExecute = string::format($uDataset->queryString, $tArray);

					$tData = array(
						'data' => $this->querySet($tQueryExecute)
					);

					if($this->inTransaction) {
						$this->commit();
					}

					if($uDataset->cacheLife > 0) {
						$this->cache[$uPropsSerialized] = &$tData;
						cache::set($tFolder, $uPropsSerialized, $tData);
					}
				}
				catch(PDOException $ex) {
					if($this->inTransaction) {
						$this->rollBack();
					}

					throw new PDOException($ex->getMessage());
				}

				$this->stats['query']++;
			} else {
				$this->stats['cache']++;
			}

			return $tData;
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
			$this->id = $uConfig['@id'];
			$this->queryString = $uConfig['.'];
			$this->parameters = strlen($uConfig['@parameters']) > 0 ? explode(',', $uConfig['@parameters']) : array();
			$this->cacheLife = isset($uConfig['@cacheLife']) ? (int)$uConfig['@cacheLife'] : 0;
			$this->transaction = isset($uConfig['@transaction']);
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
		public function __construct(&$uDatabase = null) {
			$this->setDatabase($uDatabase);
		}

		/**
		* @ignore
		*/
		public function setDatabase(&$uDatabase = null) {
			if(!is_null($uDatabase)) {
				$this->database = &$uDatabase;
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
		}

		/**
		* @ignore
		*/
		public function setTable($uTableName) {
			$this->table = $uTableName;

			return $this;
		}

		/**
		* @ignore
		*/
		public function joinTable($uTableName, $uCondition, $uJoinType = 'INNER') {
			$this->table .= ' ' . $uJoinType . ' JOIN ' . $uTableName . ' ON ' . $uCondition;

			return $this;
		}

		/**
		* @ignore
		*/
		public function setFields($uArray) {
			foreach($uArray as $tField => &$tValue) {
				// $this->fields[$tField] = '\'' . string::squote($tValue) . '\'';
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
		public function setFieldsDirect($uArray) {
			$this->fields = &$uArray;

			return $this;
		}

		/**
		* @ignore
		*/
		public function addField($uField, $uValue = null) {
			if(func_num_args() == 1) {
				$this->fields[] = $uField;

				return $this;
			}

			if(is_null($uValue)) {
				$this->fields[$uField] = 'NULL';
			}
			else {
				// $this->fields[$uField] = '\'' . string::squote($uValue) . '\'';
				$this->fields[$uField] = ':' . $uField;
				$this->parameters[$this->fields[$uField]] = $uValue;
			}

			return $this;
		}

		/**
		* @ignore
		*/
		public function addFieldDirect($uField, $uValue) {
			$this->fields[$uField] = $uValue;

			return $this;
		}

		/**
		* @ignore
		*/
		public function addParameter($uParameter, $uValue) {
			$this->parameters[$uParameter] = $uValue;

			return $this;
		}

		/**
		* @ignore
		*/
		public function setWhere($uCondition) {
			$this->where = $uCondition;

			return $this;
		}

		/**
		* @ignore
		*/
		public function andWhere($uCondition) {
			$this->where .= ' AND ' . $uCondition;

			return $this;
		}

		/**
		* @ignore
		*/
		public function orWhere($uCondition) {
			$this->where .= ' OR ' . $uCondition;

			return $this;
		}

		/**
		* @ignore
		*/
		public function setGroupBy($uGroupBy) {
			$this->groupby = $uGroupBy;

			return $this;
		}

		/**
		* @ignore
		*/
		public function addGroupBy($uGroupBy) {
			$this->groupby .= ', ' . $uGroupBy;

			return $this;
		}

		/**
		* @ignore
		*/
		public function setOrderBy($uOrderBy, $uOrder = null) {
			$this->orderby = $uOrderBy;
			if(!is_null($uOrder)) {
				$this->orderby .= ' ' . $uOrder;
			}

			return $this;
		}

		/**
		* @ignore
		*/
		public function addOrderBy($uOrderBy, $uOrder = null) {
			$this->orderby .= ', ' . $uOrderBy;
			if(!is_null($uOrder)) {
				$this->orderby .= ' ' . $uOrder;
			}

			return $this;
		}

		/**
		* @ignore
		*/
		public function setLimit($uLimit) {
			$this->limit = $uLimit;

			return $this;
		}

		/**
		* @ignore
		*/
		public function setOffset($uOffset) {
			$this->offset = $uOffset;

			return $this;
		}

		/**
		* @ignore
		*/
		public function setSequence($uSequence) {
			$this->sequence = $uSequence;

			return $this;
		}

		/**
		* @ignore
		*/
		public function setReturning($uReturning) {
			$this->returning = $uReturning;

			return $this;
		}

		/**
		* @ignore
		*/
		public function insert() {
			$tQuery = database::sqlInsert($this->table, $this->fields, $this->returning);

			if(strlen($this->returning) > 0) {
				$tInsertId = $this->database->queryScalar($tQuery, $this->parameters);
			}
			else {
				$this->database->query($tQuery, $this->parameters);

				if(!is_null($this->sequence) && strlen($this->sequence) > 0) {
					$tInsertId = $this->database->lastInsertId($this->sequence);
				}
				else {
					$tInsertId = $this->database->lastInsertId();
				}
			}

			$this->clear();

			return $tInsertId;
		}

		/**
		* @ignore
		*/
		public function update() {
			if($this->database->driver == 'mysql' && $this->limit >= 0) {
				$tExtra = 'LIMIT ' . $this->limit;
			}
			else {
				$tExtra = '';
			}

			$this->database->query(database::sqlUpdate($this->table, $this->fields, $this->where, $tExtra), $this->parameters);

			$this->clear();

			return $this->database->affectedRows();
		}

		/**
		* @ignore
		*/
		public function delete() {
			if($this->database->driver == 'mysql' && $this->limit >= 0) {
				$tExtra = 'LIMIT ' . $this->limit;
			}
			else {
				$tExtra = '';
			}

			$this->database->query(database::sqlDelete($this->table, $this->where, $tExtra), $this->parameters);

			$this->clear();

			return $this->database->affectedRows();
		}

		/**
		* @ignore
		*/
		public function &get() {
			if($this->limit >= 0) {
				if($this->offset >= 0) {
					$tExtra = 'LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
				}
				else {
					$tExtra = 'LIMIT ' . $this->limit;
				}
			}
			else {
				$tExtra = '';
			}

			$tReturn = $this->database->querySet(database::sqlSelect($this->table, $this->fields, $this->where, $this->orderby, $tExtra), $this->parameters);

			$this->clear();

			return $tReturn;
		}

		/**
		* @ignore
		*/
		public function &getRow() {
			if($this->limit >= 0) {
				if($this->offset >= 0) {
					$tExtra = 'LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
				}
				else {
					$tExtra = 'LIMIT ' . $this->limit;
				}
			}
			else {
				$tExtra = '';
			}

			$tReturn = $this->database->queryRow(database::sqlSelect($this->table, $this->fields, $this->where, $this->orderby, $tExtra), $this->parameters);

			$this->clear();

			return $tReturn;
		}

		/**
		* @ignore
		*/
		public function &getScalar() {
			if($this->limit >= 0) {
				if($this->offset >= 0) {
					$tExtra = 'LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
				}
				else {
					$tExtra = 'LIMIT ' . $this->limit;
				}
			}
			else {
				$tExtra = '';
			}

			$tReturn = $this->database->queryScalar(database::sqlSelect($this->table, $this->fields, $this->where, $this->orderby, $tExtra), $this->parameters);

			$this->clear();

			return $tReturn;
		}

		/**
		* @ignore
		*/
		public function &calculate($uTable, $uOperation = 'COUNT', $uField = '*', $uWhere = null) {
			$tReturn = $this->database->queryScalar(database::sqlSelect($uTable, array($uOperation . '(' . $uField . ')'), $uWhere, null, null), array());

			return $tReturn;
		}
	}

	/**
	* DataRows Iterator Class
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*/
	class dataRowsIterator extends NoRewindIterator implements Countable {
		/**
		* @ignore
		*/
		public $connection;
		/**
		* @ignore
		*/
		public $current;
		/**
		* @ignore
		*/
		public $count;
		/**
		* @ignore
		*/
		public $cursor = 0;

		/**
		* @ignore
		*/
		public function __construct($uConnection) {
			$this->connection = &$uConnection;
			$this->count = $this->connection->rowCount();

			$this->current = $this->connection->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
		}

		/**
		* @ignore
		*/
		public function __destruct() {
			$this->connection->closeCursor();
		}

		/**
		* @ignore
		*/
		public function count() {
			return $this->count;
		}

		/**
		* @ignore
		*/
		public function current() {
			return $this->current;
		}

		/**
		* @ignore
		*/
		public function key() {
			return null;
		}

		/**
		* @ignore
		*/
		public function next() {
			$this->cursor++;
			$this->current = $this->connection->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
			return $this->current;
		}

		/**
		* @ignore
		*/
		public function valid() {
			if($this->cursor < $this->count) {
				return true;
			}

			return false;
		}
	}
}

?>