<?php

	/**
	* Database Provider Mysql Extension
	*
	* @package Scabbia
	* @subpackage databaseprovider_mysql
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends database
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends mysqli
	*/
	class databaseprovider_mysql {
		/**
		* @ignore
		*/
		public $standard = null;
		/**
		* @ignore
		*/
		public $host;
		/**
		 * @ignore
		 */
		public $database;
		/**
		* @ignore
		*/
		public $username;
		/**
		* @ignore
		*/
		public $password;
		/**
		* @ignore
		*/
		public $persistent;

		/**
		* @ignore
		*/
		public function __construct($uConfig) {
			$this->host = $uConfig['host'];
			$this->database = $uConfig['database'];
			$this->username = $uConfig['username'];
			$this->password = $uConfig['password'];

			$this->persistent = isset($uConfig['persistent']);
		}

		/**
		* @ignore
		*/
		public function open() {
			$tParms = array();
			// if($this->persistent) {
			// }

			$this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
			if($this->connection->connect_errno > 0) {
				throw new Exception('Mysql Exception: ' . $this->connection->connect_error);
			}
		}

		/**
		* @ignore
		*/
		public function close() {
		}

		/**
		* @ignore
		*/
		public function beginTransaction() {
			$this->connection->autocommit(false);
		}

		/**
		* @ignore
		*/
		public function commit() {
			$this->connection->commit();
		}

		/**
		* @ignore
		*/
		public function rollBack() {
			$this->connection->rollback();
		}

		/**
		* @ignore
		*/
		public function execute($uQuery) {
			return $this->connection->query($uQuery);
		}

		/**
		* @ignore
		*/
		public function &queryDirect($uQuery, $uParameters = array()) {
			$tQuery = $this->connection->prepare($uQuery);

			foreach($uParameters as &$tParameter) {
				switch(gettype($tParameter)) {
				case 'integer':
					$tType = 'i';
					break;
				case 'double':
					$tType = 'd';
					break;
				default:
					$tType = 's';
					break;
				}

				$tQuery->bind_param($tType, $tParameter);
			}

			$tResult = $tQuery->execute();

			return $tQuery;
		}

		/**
		* @ignore
		*/
		public function itSeek(&$uObject, $uRow) {
			$uObject->data_seek($uRow);

			return $this->itNext($uObject);
		}

		/**
		* @ignore
		*/
		public function itNext(&$uObject) {
			return $uObject->fetch();
		}

		/**
		* @ignore
		*/
		public function itCount(&$uObject) {
			return $uObject->num_rows;
		}

		/**
		* @ignore
		*/
		public function itClose(&$uObject) {
			return $uObject->close();
		}

		/**
		* @ignore
		*/
		public function lastInsertId($uName = null) {
			return $this->connection->insert_id;
		}

		/**
		* @ignore
		*/
		public function serverInfo() {
			return $this->connection->server_info;
		}

		/**
		* @ignore
		*/
		public function sqlInsert($uTable, $uObject, $uReturning = '') {
			$tSql =
				'INSERT INTO ' . $uTable . ' ('
				. implode(', ', array_keys($uObject))
				. ') VALUES ('
				. implode(', ', array_values($uObject))
				. ')';

			// if(strlen($uReturning) > 0) {
			// 	$tSql .= ' RETURNING ' . $uReturning;
			// }

			return $tSql;
		}

		/**
		* @ignore
		*/
		public function sqlUpdate($uTable, $uObject, $uWhere, $uExtra = null) {
			$tPairs = array();
			foreach($uObject as $tKey => &$tValue) {
				$tPairs[] = $tKey . '=' . $tValue;
			}

			$tSql = 'UPDATE ' . $uTable . ' SET '
				. implode(', ', $tPairs);

			if(strlen($uWhere) > 0) {
				$tSql .= ' WHERE ' . $uWhere;
			}

			if(!is_null($uExtra)) {
				if(isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
					$tSql .= ' LIMIT ' . $uExtra['limit'];
				}
			}

			return $tSql;
		}

		/**
		* @ignore
		*/
		public function sqlDelete($uTable, $uWhere, $uExtra = null) {
			$tSql = 'DELETE FROM ' . $uTable;

			if(strlen($uWhere) > 0) {
				$tSql .= ' WHERE ' . $uWhere;
			}

			if(!is_null($uExtra)) {
				if(isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
					$tSql .= ' LIMIT ' . $uExtra['limit'];
				}
			}

			return $tSql;
		}

		/**
		* @ignore
		*/
		public function sqlSelect($uTable, $uFields, $uWhere, $uOrderBy, $uGroupBy, $uExtra = null) {
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

			if(!is_null($uGroupBy) && strlen($uGroupBy) > 0) {
				$tSql .= ' GROUP BY ' . $uGroupBy;
			}

			if(!is_null($uOrderBy) && strlen($uOrderBy) > 0) {
				$tSql .= ' ORDER BY ' . $uOrderBy;
			}

			if(!is_null($uExtra)) {
				if(isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
					if(isset($uExtra['offset']) && $uExtra['offset'] >= 0) {
						$tSql .= ' LIMIT ' . $uExtra['offset'] . ', ' . $uExtra['limit'];
					}
					else {
						$tSql .= ' LIMIT ' . $uExtra['limit'];
					}
				}
			}

			return $tSql;
		}
	}

?>