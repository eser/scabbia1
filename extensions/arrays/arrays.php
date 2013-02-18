<?php

	namespace Scabbia;

	/**
	 * Arrays Extension
	 *
	 * @package Scabbia
	 * @subpackage arrays
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class arrays {
		/**
		 * @ignore
		 */
		public static function flat() {
			$tArray = array();

			foreach(func_get_args() as $tValue) {
				if(is_array($tValue)) {
					foreach(call_user_func_array('self::flat', $tValue) as $tValue2) {
						$tArray[] = $tValue2;
					}

					continue;
				}

				$tArray[] = $tValue;
			}

			return $tArray;
		}

		/**
		 * Gets the first element in array, otherwise returns default value.
		 */
		public static function getFirst($uArray, $uDefault = null) {
			$tValue = current($uArray);
			if($tValue === false) {
				return $uDefault;
			}

			return $tValue;
		}

		/**
		 * Gets the specified element in array, otherwise returns default value.
		 *
		 * @param array $uArray array
		 * @param mixed $uElement key
		 * @param mixed $uDefault default value
		 *
		 * @return mixed|null
		 */
		public static function get($uArray, $uElement, $uDefault = null) {
			if(!isset($uArray[$uElement])) {
				return $uDefault;
			}

			return $uArray[$uElement];
		}

		/**
		 * Gets the specified elements in array.
		 *
		 * @param array $uArray array
		 *
		 * @internal param mixed $uElement key
		 * @return array
		 */
		public static function getArray($uArray) {
			$tReturn = array();

			foreach(array_slice(func_get_args(), 1) as $tKey) {
				$tReturn[$tKey] = $uArray[$tKey];
			}

			return $tReturn;
		}

		/**
		 * Gets a random element in array.
		 *
		 * @param array $uArray array
		 *
		 * @return null
		 */
		public static function getRandom($uArray) {
			$tCount = count($uArray);
			if($tCount == 0) {
				return null;
			}

			$uValues = array_values($uArray);

			return $uValues[rand(0, $tCount - 1)];
		}

		/**
		 * @ignore
		 */
		public static function getPath($uArray, $uPath, $uDivider = '.') {
			$tVariable = $uArray;

			foreach(explode($uDivider, $uPath) as $tKey) {
				$tVariable = $tVariable[$tKey];
			}

			return $tVariable;
		}

		/**
		 * Returns an array filled with the elements in specified range.
		 *
		 * @param int $uMinimum minumum number
		 * @param int $uMaximum maximum number
		 * @param bool $uWithKeys whether set keys or not
		 *
		 * @return array
		 */
		public static function range($uMinimum, $uMaximum, $uWithKeys = false) {
			$tReturn = array();

			for($i = $uMinimum; $i <= $uMaximum; $i++) {
				if($uWithKeys) {
					$tReturn[$i] = $i;
					continue;
				}

				$tReturn[] = $i;
			}

			return $tReturn;
		}

		/**
		 * Sorts an array by key.
		 *
		 * @param array $uArray array
		 * @param mixed $uField field
		 * @param string $uOrder order
		 *
		 * @return array
		 */
		public static function sortByKey($uArray, $uField, $uOrder = 'asc') {
			$tReturn = array();
			if(count($uArray) == 0) {
				return $tReturn;
			}

			$tValues = array();
			foreach($uArray as $tKey => $tValue) {
				$tValues[$tKey] = $tValue[$uField];
			}

			if($uOrder == 'desc') {
				arsort($tValues);
			}
			else {
				asort($tValues);
			}

			foreach(array_keys($tValues) as $tKey) {
				$tReturn[] = $uArray[$tKey];
			}

			return $tReturn;
		}

		/**
		 * Categorizes an array by key.
		 *
		 * @param array $uArray array
		 * @param mixed $uKey key
		 *
		 * @return array
		 */
		public static function categorize($uArray, $uKey) {
			$tReturn = array();
			if(!is_array($uKey)) {
				$uKey = array($uKey);
			}

			foreach($uArray as &$tRow) {
				$tRef = & $tReturn;
				foreach($uKey as $tKey) {
					$tValue = $tRow[$tKey];
					if(!isset($tRef[$tValue])) {
						$tRef[$tValue] = array();
					}
					$tNewRef = & $tRef[$tValue];
					unset($tRef);
					$tRef = & $tNewRef;
				}

				$tRef[] = $tRow;
			}

			return $tReturn;
		}

		/**
		 * ....
		 *
		 * @param array $uArray array
		 * @param mixed $uKey key
		 *
		 * @return array
		 */
		public static function assignKeys($uArray, $uKey) {
			$tReturn = array();

			foreach($uArray as $tRow) {
				$tReturn[$tRow[$uKey]] = $tRow;
			}

			return $tReturn;
		}

		/**
		 * Extracts specified column from the array.
		 *
		 * @param array $uArray array
		 * @param mixed $uKey key
		 * @param bool $uSkipEmpties whether skip empty entries or not
		 * @param bool $uDistinct whether returns multiple instances of same entries or not
		 *
		 * @return array
		 */
		public static function column($uArray, $uKey, $uSkipEmpties = false, $uDistinct = false) {
			$tReturn = array();

			foreach($uArray as $tRow) {
				if(isset($tRow[$uKey])) {
					if(!$uDistinct || !in_array($tRow[$uKey], $tReturn)) {
						$tReturn[] = $tRow[$uKey];
					}
				}
				else {
					if(!$uSkipEmpties) {
						$tReturn[] = null;
					}
				}
			}

			return $tReturn;
		}

		/**
		 * Gets the first matching row.
		 *
		 * @param array $uArray array
		 * @param mixed $uKey key
		 * @param mixed $uValue value
		 *
		 * @return bool
		 */
		public static function getRow($uArray, $uKey, $uValue) {
			foreach($uArray as $tRow) {
				if(isset($tRow[$uKey]) && $tRow[$uKey] == $uValue) {
					return $tRow;
				}
			}

			return false;
		}

		/**
		 * Gets the first matching row's key.
		 *
		 * @param array $uArray array
		 * @param mixed $uKey key
		 * @param mixed $uValue value
		 *
		 * @return bool|int|string
		 */
		public static function getRowKey($uArray, $uKey, $uValue) {
			foreach($uArray as $tKey => $tRow) {
				if(isset($tRow[$uKey]) && $tRow[$uKey] == $uValue) {
					return $tKey;
				}
			}

			return false;
		}

		/**
		 * Gets the matching rows.
		 *
		 * @param array $uArray array
		 * @param mixed $uKey key
		 * @param mixed $uValue value
		 *
		 * @return array
		 */
		public static function getRows($uArray, $uKey, $uValue) {
			$tReturn = array();

			foreach($uArray as $tKey => $tRow) {
				if(isset($tRow[$uKey]) && $tRow[$uKey] == $uValue) {
					$tReturn[$tKey] = $tRow;
				}
			}

			return $tReturn;
		}

		/**
		 * Combines two array properly.
		 *
		 * @param array $uArray1 first array
		 * @param array $uArray2 second array
		 *
		 * @return array
		 */
		public static function combine($uArray1, $uArray2) {
			$tArray = array();

			for($i = 0, $tLen = count($uArray1); $i < $tLen; $i++) {
				if(!isset($uArray2[$i])) {
					$tArray[$uArray1[$i]] = null;
					continue;
				}

				$tArray[$uArray1[$i]] = $uArray2[$i];
			}

			return $tArray;
		}

		/**
		 * Combines two array properly.
		 */
		public static function combine2() {
			$uArgs = func_get_args();
			$tArray = array();

			for($i = 0; true; $i++) {
				$tValues = array();
				$tAllNull = true;

				foreach($uArgs as $tArg) {
					if(isset($tArg[$i])) {
						$tAllNull = false;
						$tValues[] = $tArg[$i];
						continue;
					}

					$tValues[] = null;
				}

				if($tAllNull === true) {
					break;
				}

				$tArray[] = $tValues;
			}

			return $tArray;
		}

		/**
		 * Sorts an array by priority list.
		 *
		 * @param array $uArray array
		 * @param array $uPriorities priority list
		 *
		 * @return array
		 */
		public static function sortByPriority($uArray, $uPriorities) {
			$tArray = array();

			foreach($uPriorities as $tKey) {
				if(!isset($uArray[$tKey])) {
					continue;
				}

				$tArray[$tKey] = $uArray[$tKey];
			}

			// union of arrays
			return $tArray + $uArray;
		}
	}

	?>