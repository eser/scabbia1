<?php

if(extensions::isSelected('arrays')) {
	/**
	* Arrays Extension
	*
	* @package Scabbia
	* @subpackage UtilityExtensions
	*/
	class arrays {
		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'arrays',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		/**
		* @ignore
		*/
		public static function get($uArray, $uElement, $uDefault = null) {
			if(!isset($uArray[$uElement])) {
				return $uDefault;
			}

			return $uArray[$uElement];
		}

		/**
		* @ignore
		*/
		public static function getArray() {
			$uArgs = func_get_args();
			$uArray = array_shift($uArgs);
			$tReturn = array();

			foreach(array_keys($uArgs) as $tKey) {
				$tReturn[$tKey] = $uArray[$tKey];
			}

			return $tReturn;
		}

		/**
		* @ignore
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
		* @ignore
		*/
		public static function sortByKey($uArray, $uField, $uOrder = 'asc') {
            if(count($uArray) == 0) {
				return;
			}

			$tValues = array();
			foreach ($uArray as $tKey => $tValue) {
				$tValues[$tKey] = $tValue[$uField];
			}

			if($uOrder == 'desc') {
				arsort($tValues);
			}
			else {
				asort($tValues);
			}

			$tReturn = array();
			foreach(array_keys($tValues) as $tKey) {
				$tReturn[] = $uArray[$tKey];
			}

			return $tReturn;
		}

		/**
		* @ignore
		*/
		public static function &categorize($uArray, $uKey) {
			$tReturn = array();

			foreach($uArray as &$tRow) {
				$tKey = $tRow[$uKey];
				if(!isset($tReturn[$tKey])) {
					$tReturn[$tKey] = array();
				}

				$tReturn[$tKey][] = $tRow;
			}

			return $tReturn;
		}

		/**
		* @ignore
		*/
		public static function &column($uArray, $uKey, $uSkipEmpties = false) {
			$tReturn = array();

			foreach($uArray as &$tRow) {
				if(isset($tRow[$tKey])) {
					$tReturn[] = $tRow[$tKey];
				}
				else if(!$uSkipEmpties) {
					$tReturn[] = null;
				}
			}

			return $tReturn;
		}

		/**
		* @ignore
		*/
		public static function getRow($uArray, $uKey, $uValue) {
			foreach($uArray as &$tRow) {
				if(isset($tRow[$tKey]) && $tRow[$tKey] == $uValue) {
					return $tRow;
				}
			}

			return false;
		}

		/**
		* @ignore
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
		* @ignore
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
}

?>