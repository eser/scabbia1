<?php

if(extensions::isSelected('databaseprovider_pdo')) {
	/**
	* Database PDO Extension
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*/
	class databaseprovider_pdo {
		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'databaseprovider: pdo',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array('pdo'),
				'fwversion' => '1.0',
				'fwdepends' => array('database')
			);
		}

		/**
		* @ignore
		*/
		public $standard = null;
		/**
		* @ignore
		*/
		public $pdoString;
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
		public $overrideCase;
		/**
		* @ignore
		*/
		public $persistent;
		/**
		* @ignore
		*/
		public $fetchMode;
		/**
		* @ignore
		*/
		public $affectedRows;

		/**
		* @ignore
		*/
		public function __construct($uConfig) {
			$this->pdoString = $uConfig['pdoString']['.'];
			$this->username = $uConfig['username']['.'];
			$this->password = $uConfig['password']['.'];

			if(isset($uConfig['overrideCase'])) {
				$this->overrideCase = $uConfig['overrideCase']['.'];
			}

			$this->persistent = isset($uConfig['persistent']);
			$this->fetchMode = PDO::FETCH_ASSOC;
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

			$this->standard = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);
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
			$this->connection->beginTransaction();
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
			$this->connection->rollBack();
		}

		/**
		* @ignore
		*/
		public function exec($uQuery) {
			$this->connection->exec($uQuery);
		}
		
		/**
		* @ignore
		*/
		public function query($uQuery, $uParameters = array()) {
			$tQuery = $this->connection->prepare($uQuery);
			$tResult = $tQuery->execute($uParameters);
			// $tQuery->closeCursor();
			$this->affectedRows = $tQuery->rowCount();

			if($tResult) {
				return $this->affectedRows;
			}

			return false;
		}

		/**
		* @ignore
		*/
		public function &queryFetch($uQuery, $uParameters = array()) {
			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);

			$this->affectedRows = $tQuery->rowCount();

			$tIterator = new dataRowsIterator($tQuery, $this);
			return $tIterator;
		}

		/**
		* @ignore
		*/
		public function &querySet($uQuery, $uParameters = array()) {
			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);
			$tResult = $tQuery->fetchAll(PDO::FETCH_ASSOC);
			$this->affectedRows = $tQuery->rowCount();
			$tQuery->closeCursor();

			return $tResult;
		}

		/**
		* @ignore
		*/
		public function &queryRow($uQuery, $uParameters = array()) {
			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);
			$tResult = $tQuery->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
			$this->affectedRows = $tQuery->rowCount();
			$tQuery->closeCursor();

			return $tResult;
		}

		/**
		* @ignore
		*/
		public function &queryScalar($uQuery, $uParameters = array()) {
			$tQuery = $this->connection->prepare($uQuery);
			$tQuery->execute($uParameters);
			$tResult = $tQuery->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT);
			$this->affectedRows = $tQuery->rowCount();
			$tQuery->closeCursor();

			return $tResult[0];
		}

		/**
		* @ignore
		*/
		public function itNext(&$uObject) {
			return $uObject->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
		}

		/**
		* @ignore
		*/
		public function itCount(&$uObject) {
			return $uObject->rowCount();
		}

		/**
		* @ignore
		*/
		public function itClose(&$uObject) {
			return $uObject->closeCursor();
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
	}
}

?>