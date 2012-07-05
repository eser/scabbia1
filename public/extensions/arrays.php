<?php

if(extensions::isSelected('arrays')) {
	/**
	* Arrays Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class arrays {
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
		
		public static function get($uArray, $uElement, $uDefault = null) {
			if(!isset($uArray[$uElement])) {
				return $uDefault;
			}

			return $uArray[$uElement];
		}

		public static function getArray() {
			$uArgs = func_get_args();
			$uArray = array_shift($uArgs);
			$tReturn = array();

			foreach(array_keys($uArgs) as $tKey) {
				$tReturn[$tKey] = $uArray[$tKey];
			}

			return $tReturn;
		}

		public static function getRandom($uArray) {
			$tCount = count($uArray);
			if($tCount == 0) {
				return null;
			}

			$uValues = array_values($uArray);
			return $uValues[rand(0, $tCount - 1)];
		}

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
