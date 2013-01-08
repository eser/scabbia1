<?php

	namespace Scabbia;

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
		public function __construct($uDatabase = null) {
			$this->setDatabase($uDatabase);
		}

		/**
		 * @ignore
		 */
		public function setDatabase($uDatabase = null) {
			if(!is_null($uDatabase)) {
				$this->database = $uDatabase;
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
			foreach($uArray as $tField => $tValue) {
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
		public function setFieldsDirect($uArray) {
			$this->fields = $uArray;

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
				// $this->fields[$uField] = string::squote($uValue, true);
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
		private static function constructWhere($uArray, $uIsList = false) {
			$tOutput = '(';
			$tPreviousElement = null;

			foreach($uArray as $tElement) {
				if(is_array($tElement)) {
					$tOutput .= self::constructWhere($tElement, ($tPreviousElement == _IN || $tPreviousElement == _NOTIN));
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
		public function setWhere($uCondition, $uList = null) {
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
		public function andWhere($uCondition, $uList = null, $uKeyword = 'OR') {
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
		public function orWhere($uCondition, $uList = null, $uKeyword = 'AND') {
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
		public function setCaching($uCaching) {
			$this->caching = $uCaching;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function setDebug($uDebug) {
			$this->debug = $uDebug;

			return $this;
		}

		/**
		 * @ignore
		 */
		public function insert() {
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
		public function update() {
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
		public function delete() {
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
		public function get() {
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
		public function calculate($uOperation = 'COUNT') {
			$tQuery = $this->database->provider->sqlSelect($this->table, array($uOperation . '(' . $this->fields[0] . ')'), $this->where, null, $this->groupby);
			if($this->debug) {
				echo 'Calculate Query: ', $tQuery;
			}
			$tReturn = $this->database->query(
				$tQuery,
				$this->parameters,
				$this->caching
			);

			$this->clear();

			return $tReturn->scalar();
		}

		/**
		 * @ignore
		 */
		public function runSmartObject($uInstance) {
			$tRow = $this->getRow();

			foreach($tRow as $tKey => $tValue) {
				$uInstance->obtained[$tKey] = $tValue;
			}
		}
	}

	?>