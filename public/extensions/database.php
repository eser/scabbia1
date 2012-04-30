<?php

if(Extensions::isSelected('database')) {
	class database {
		public static $databases = array();
		public static $default = null;

		public static function extension_info() {
			return array(
				'name' => 'database',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array('pdo'),
				'fwversion' => '1.0',
				'fwdepends' => array('string', 'io')
			);
		}

		public static function extension_load() {
			foreach(config::get('/databaseList', array()) as $tDatabaseConfig) {
				$tDatabase = new DatabaseConnection($tDatabaseConfig);
				self::$databases[$tDatabase->id] = $tDatabase;

				if(is_null(self::$default) || $tDatabase->default) {
					self::$default = $tDatabase;
				}
			}
		}

		public static function &get() {
			$uArgs = func_get_args();

			switch(count($uArgs)) {
			case 0:
				return self::$default;
				break;
			case 1:
				return self::$databases[$uArgs[0]];
				break;
			case 2:
				return self::$databases[$uArgs[0]]->datasets[$uArgs[1]];
				break;
			}

			return null;
		}
		
		public static function sqlInsert($uTable, $uObject) {
			$tSql =
				'INSERT INTO ' . $uTable . ' ('
				. implode(', ', array_keys($uObject))
				. ') VALUES ('
				. implode(', ', array_values($uObject))
				. ')';

			return $tSql;
		}

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

		public static function sqlSelect($uTable, $uFields, $uWhere, $uExtra = '') {
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

			if(strlen($uExtra) > 0) {
				$tSql .= ' ' . $uExtra;
			}

			return $tSql;
		}
	}

	class DatabaseConnection {
		public $id;
		public $default;
		protected $connection = null;
		public $driver = null;
		public $datasets = array();
		public $cache = array();
		public $stats = array('cache' => 0, 'query' => 0);
		public $active = false;
		public $inTransaction = false;
		protected $pdoString;
		protected $username;
		protected $password;
		protected $initCommand;
		protected $overrideCase;
		protected $persistent;
		public $keyphase = null;
		public $cachePath;
		private $affectedRows;

		public function __construct($uConfig) {
			$this->id = $uConfig['@id'];
			$this->default = isset($uConfig['@default']);
			$this->pdoString = $uConfig['pdoString']['.'];
			$this->username = $uConfig['username']['.'];
			$this->password = $uConfig['password']['.'];

			if(isset($uConfig['initCommand'])) {
				$this->initCommand = $uConfig['initCommand']['.'];
			}

			if(isset($uConfig['overrideCase'])) {
				$this->overrideCase = $uConfig['overrideCase']['.'];
			}
			
			if(isset($uConfig['@keyphase'])) {
				$this->keyphase = $uConfig['@keyphase'];
			}

			$this->persistent = isset($uConfig['persistent']);
			if(isset($uConfig['cachePath'])) {
				$this->cachePath = Framework::translatePath($uConfig['cachePath']['.']);
			}
			else {
				$this->cachePath = QPATH_APP . 'writable/datasetCache/';
			}

			foreach($uConfig['datasetList'] as &$tDatasetConfig) {
				$tDataset = new DatabaseDataset($this, $tDatasetConfig);
				$this->datasets[$tDataset->id] = $tDataset;
			}
		}
		
		public function __destruct() {
			if($this->active) {
				$this->close();
			}
		}
		
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
		
		public function close() {
			$this->active = false;
		}

		public function beginTransaction() {
			$this->open();
			$this->connection->beginTransaction();
			$this->inTransaction = true;
		}

		public function commit() {
			$this->connection->commit();
			$this->inTransaction = false;
		}

		public function rollBack() {
			$this->connection->rollBack();
			$this->inTransaction = false;
		}

		public function query($uQuery, $uParameters = array()) {
			$this->open();
			$tQuery = $this->connection->prepare($uQuery);
			$tResult = $tQuery->execute($uParameters);
			// $tQuery->closeCursor();
			$this->affectedRows = $tQuery->rowCount();
			
			if($tResult) {
				return $this->affectedRows;
			}

			return false;
		}

		public function &queryFetch($uQuery, $uParameters = array()) {
			$this->open();
			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);

			$tIterator = new DataRowsIterator($tQuery);
			return $tIterator;
		}

		public function &querySet($uQuery, $uParameters = array()) {
			$this->open();
			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);
			$tResult = $tQuery->fetchAll(PDO::FETCH_ASSOC);
			// $this->affectedRows = $tQuery->rowCount();
			$tQuery->closeCursor();

			return $tResult;
		}

		public function &queryRow($uQuery, $uParameters = array()) {
			$this->open();
			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);
			$tResult = $tQuery->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
			// $this->affectedRows = $tQuery->rowCount();
			$tQuery->closeCursor();

			return $tResult;
		}

		public function &queryScalar($uQuery, $uParameters = array()) {
			$this->open();
			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);
			$tResult = $tQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
			// $this->affectedRows = $tQuery->rowCount();
			$tQuery->closeCursor();

			return $tResult[0];
		}

		public function lastInsertId($uName = null) {
			return $this->connection->lastInsertId($uName);
		}

		public function affectedRows() {
			return $this->affectedRows;
		}

		public function serverInfo() {
			return $this->connection->getAttribute(PDO::ATTR_SERVER_INFO);
		}
	}

	class DatabaseDataset {
		protected $database;
		public $id;
		public $queryString;
		public $parameters;
		public $cacheLife;
		public $transaction;

		public function __construct(&$uDatabase, $uConfig) {
			$this->database = &$uDatabase;

			$this->id = $uConfig['@id'];
			$this->queryString = $uConfig['.'];
			$this->parameters = strlen($uConfig['@parameters']) > 0 ? explode(',', $uConfig['@parameters']) : array();
			$this->cacheLife = isset($uConfig['@cacheLife']) ? (int)$uConfig['@cacheLife'] : 0;
			$this->transaction = isset($uConfig['@transaction']);
		}

		public function query() {
			$uProps = func_get_args();

			return $this->queryInternal($uProps);
		}

		public function &queryFetch($uQuery, $uParameters = array()) {
			$uProps = func_get_args();

			return $this->queryFetchInternal($uProps);
		}

		public function querySet() {
			$uProps = func_get_args();
			$tData = $this->querySetInternal($uProps);

			return $tData['data'];
		}

		public function queryRow() {
			$uProps = func_get_args();
			$tData = $this->querySetInternal($uProps);

			if(count($tData['data']) > 0) {
				return $tData['data'][0];
			}

			return null;
		}

		public function queryScalar() {
			$uProps = func_get_args();
			$tData = $this->querySetInternal($uProps);

			if(count($tData['data']) > 0) {
				return current($tData['data'][0]);
			}

			return null;
		}

		private function queryInternal($uProps) {
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

			if($this->transaction) {
				$this->database->beginTransaction();
			}

			try {
				$tCount = 0;
				$tArray = array();

				foreach($this->parameters as &$tParam) {
					$tArray[$tParam] = $uProps[$tCount++];
				}

				$tQueryExecute = string::format($this->queryString, $tArray);

				if(Framework::$debug) {
					echo 'query: ', $tQueryExecute, "\n";
				}

				$tResult = $this->database->query($tQueryExecute);

				if($this->database->inTransaction) {
					$this->database->commit();
				}
			}
			catch(PDOException $ex) {
				if($this->database->inTransaction) {
					$this->database->rollBack();
				}

				throw new PDOException($ex->getMessage());
			}

			$this->database->stats['query']++;

			if(isset($tResult)) {
				return $this->database->affectedRows();
			}

			return false;
		}

		private function &queryFetchInternal($uProps) {
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

			if($this->transaction) {
				$this->database->beginTransaction();
			}

			try {
				$tCount = 0;
				$tArray = array();

				foreach($this->parameters as &$tParam) {
					$tArray[$tParam] = $uProps[$tCount++];
				}

				$tQueryExecute = string::format($this->queryString, $tArray);

				if(Framework::$debug) {
					echo 'query: ', $tQueryExecute, "\n";
				}

				$tResult = $this->database->queryFetch($tQueryExecute);

				if($this->database->inTransaction) {
					$this->database->commit();
				}
			}
			catch(PDOException $ex) {
				if($this->database->inTransaction) {
					$this->database->rollBack();
				}

				throw new PDOException($ex->getMessage());
			}

			$this->database->stats['query']++;

			if(isset($tResult)) {
				return $tResult;
			}

			return false;
		}

		private function &querySetInternal($uProps) {
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

			$uPropsSerialized = $this->id;
			foreach($uProps as &$tProp) {
				$uPropsSerialized .= '_' . io::sanitize($tProp);
			}

			$tFileName = $this->database->id . '_' . $uPropsSerialized;
			$tFilePath = $this->database->cachePath . $tFileName;

			$tData = null;
			$tLoadedFromCache = false;

			if(isset($this->database->cache[$uPropsSerialized])) {
				$tData = &$this->database->cache[$uPropsSerialized];
				$tData['data']->iterator->rewind(); // rewind ArrayIterator
				$tLoadedFromCache = true;
			}
			else if($this->cacheLife > 0 && is_readable($tFilePath)) {
				$tData = io::readSerialize($tFilePath, $this->database->keyphase);
				$tLoadedFromCache = true;

				$this->database->cache[$uPropsSerialized] = &$tData;
			}

			if(is_null($tData) || ($tData['lastmod'] + $this->cacheLife < time())) {
				if($this->transaction) {
					$this->database->beginTransaction();
				}

				try {
					$tCount = 0;
					$tArray = array();

					foreach($this->parameters as &$tParam) {
						$tArray[$tParam] = $uProps[$tCount++];
					}

					$tQueryExecute = string::format($this->queryString, $tArray);

					if(Framework::$debug) {
						echo 'query: ', $tQueryExecute, "\n";
					}

					$tData = array(
						'data' => $this->database->querySet($tQueryExecute),
						'lastmod' => time()
					);

					if($this->database->inTransaction) {
						$this->database->commit();
					}

					if($this->cacheLife > 0) {
						$this->database->cache[$uPropsSerialized] = &$tData;
						io::writeSerialize($tFilePath, $tData, $this->database->keyphase);
					}
				}
				catch(PDOException $ex) {
					if($this->database->inTransaction) {
						$this->database->rollBack();
					}

					throw new PDOException($ex->getMessage());
				}

				$this->database->stats['query']++;
			} else {
				$this->database->stats['cache']++;
			}

			return $tData;
		}
	}
	
	class DatabaseQuery {
		protected $database = null;

		private $table;
		private $fields;
		private $parameters;
		private $where;
		private $groupby;
		private $orderby;
		private $limit;
		private $offset;

		public function __construct(&$uDatabase = null) {
			$this->setDatabase($uDatabase);
		}

		public function setDatabase(&$uDatabase = null) {
			if(!is_null($uDatabase)) {
				$this->database = &$uDatabase;
			}
			else {
				$this->database = database::get(); // default
			}

			$this->clear();
		}

		public function setDatabaseName($uDatabaseName) {
			$this->database = database::get($uDatabaseName);
			$this->clear();
		}

		public function clear() {
			$this->table = '';
			$this->fields = array();
			$this->parameters = array();
			$this->where = '';
			$this->groupby = '';
			$this->orderby = '';
			$this->limit = -1;
			$this->offset = -1;
		}

		public function setTable($uTableName) {
			$this->table = $uTableName;

			return $this;
		}

		public function joinTable($uTableName, $uCondition, $uJoinType = 'INNER') {
			$this->table .= ' ' . $uJoinType . ' JOIN ' . $uTableName . ' ON ' . $uCondition;

			return $this;
		}

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

		public function setFieldsDirect($uArray) {
			$this->fields = &$uArray;

			return $this;
		}

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

		public function addFieldDirect($uField, $uValue) {
			$this->fields[$uField] = $uValue;

			return $this;
		}

		public function addParameter($uParameter, $uValue) {
			$this->parameters[$uParameter] = $uValue;

			return $this;
		}

		public function setWhere($uCondition) {
			$this->where = $uCondition;

			return $this;
		}

		public function andWhere($uCondition) {
			$this->where .= ' AND ' . $uCondition;

			return $this;
		}

		public function orWhere($uCondition) {
			$this->where .= ' OR ' . $uCondition;

			return $this;
		}

		public function setGroupBy($uGroupBy) {
			$this->groupby = $uGroupBy;

			return $this;
		}

		public function addGroupBy($uGroupBy) {
			$this->groupby .= ', ' . $uGroupBy;

			return $this;
		}

		public function setOrderBy($uOrderBy, $uOrder = 'ASC') {
			$this->orderby = $uOrderBy . ' ' . $uOrder;

			return $this;
		}

		public function addOrderBy($uOrderBy, $uOrder = 'ASC') {
			$this->orderby .= ', ' . $uOrderBy . ' ' . $uOrder;

			return $this;
		}

		public function setLimit($uLimit) {
			$this->limit = $uLimit;

			return $this;
		}

		public function setOffset($uOffset) {
			$this->offset = $uOffset;

			return $this;
		}

		public function dataset($uDataset) {
			return $this->database->datasets[$uDataset];
		}

		public function insert() {
			$this->database->query(database::sqlInsert($this->table, $this->fields), $this->parameters);

			if($this->database->driver == 'pgsql') {
				$tInsertId = $this->database->lastInsertId($this->table . '_id_seq');
			}
			else {
				$tInsertId = $this->database->lastInsertId();
			}

			$this->clear();

			return $tInsertId;
		}

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

			$tReturn = $this->database->querySet(database::sqlSelect($this->table, $this->fields, $this->where, $tExtra), $this->parameters);

			$this->clear();

			return $tReturn;
		}

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

			$tReturn = $this->database->queryRow(database::sqlSelect($this->table, $this->fields, $this->where, $tExtra), $this->parameters);

			$this->clear();

			return $tReturn;
		}

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

			$tReturn = $this->database->queryScalar(database::sqlSelect($this->table, $this->fields, $this->where, $tExtra), $this->parameters);

			$this->clear();

			return $tReturn;
		}

		public function &calculate($uTable, $uOperation = 'COUNT', $uField = '*', $uWhere = null) {
			$tReturn = $this->database->queryScalar(database::sqlSelect($uTable, array($uOperation . '(' . $uField . ')'), $uWhere, null), array());

			return $tReturn;
		}
	}

	class DataRowsIterator extends NoRewindIterator implements Countable {
		private $connection;
		private $current;
		private $count;
		private $cursor = 0;

		public function __construct($uConnection) {
			$this->connection = &$uConnection;
			$this->count = $this->connection->rowCount();

			$this->current = $this->connection->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
		}

		public function __destruct() {
			$this->connection->closeCursor();
		}

		public function count() {
			return $this->count;
		}

		public function current() {
			return $this->current;
		}

		public function key() {
			return null;
		}

		public function next() {
			$this->cursor++;
			$this->current = $this->connection->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
			return $this->current;
		}

		public function valid() {
			if($this->cursor < $this->count) {
				return true;
			}

			return false;
		}
	}
}

?>
