<?php

if(extensions::isSelected('access')) {
	/**
	* Access Extension
	*
	* @package Scabbia
	* @subpackage access
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class access {
		/**
		* @ignore
		*/
		public static $maintenance = false;
		/**
		* @ignore
		*/
		public static $maintenanceExcludeIps = array();
		/**
		* @ignore
		*/
		public static $ipFilters = array();

		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'access',
				'version' => '1.0.2',
				'phpversion' => '5.2.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
			events::register('run', 'access::run');

			self::$maintenance = (intval(config::get(config::MAIN, '/access/maintenance/mode', '0')) >= 1);
			self::$maintenanceExcludeIps = config::get(config::MAIN, '/access/maintenance/ipExcludeList', array());

			foreach(config::get(config::MAIN, '/access/ipFilter/ipFilterList', array()) as $tIpFilterList) {
				if(preg_match('/^' . str_replace(array('.', '*', '?'), array('\\.', '[0-9]{1,3}', '[0-9]{1}'), $tIpFilterList['pattern']) . '$/i', $_SERVER['REMOTE_ADDR'])) {
					if($tIpFilterList['type'] == 'allow') {
						self::$ipFilters = array();
						continue;
					}

					self::$ipFilters[] = $tIpFilterList['pattern'];
				}
			}
		}

		/**
		* @ignore
		*/
		public static function run() {
			if(self::$maintenance && !in_array($_SERVER['REMOTE_ADDR'], self::$maintenanceExcludeIps)) {
				header($_SERVER['SERVER_PROTOCOL'] . ' 503 Service Unavailable', true, 503);
				header('Retry-After: 600', true);

				$tMvcPage = config::get(config::MAIN, '/access/maintenance/mvcpage', null);
				if(!is_null($tMvcPage)) {
					mvc::view($tMvcPage);
				}
				else {
					$tFile = framework::translatePath(config::get(config::MAIN, '/access/maintenance/page'));
					include($tFile);
				}

				// to interrupt event-chain execution
				return false;
			}

			if(count(self::$ipFilters) > 0) {
				header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', true, 403);

				$tMvcPage = config::get(config::MAIN, '/access/ipFilter/mvcpage', null);
				if(!is_null($tMvcPage)) {
					mvc::view($tMvcPage);
				}
				else {
					$tFile = framework::translatePath(config::get(config::MAIN, '/access/ipFilter/page'));
					include($tFile);
				}

				// to interrupt event-chain execution
				return false;
			}
		}
	}
}

?>