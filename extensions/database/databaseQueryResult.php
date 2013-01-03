<?php

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
		public function __construct($uQuery, $uParameters, $uDatabase, $uCaching, $uDirectory, $uFilename) {
			$this->_query = $uQuery;
			$this->_parameters = $uParameters;
			$this->_database = $uDatabase;
			$this->_caching = $uCaching;
			$this->_directory = $uDirectory;
			$this->_filename = $uFilename;
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
				$this->_database->cache[$this->_filename] = $this;
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
		public function resume($uDatabase) {
			$this->_database = $uDatabase;

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