<?php

	/**
	* Users Extension
	*
	* @package Scabbia
	* @subpackage users
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends session
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class users {
		/**
		* @ignore
		*/
		public static function extension_load() {
		}

		/**
		* @ignore
		*/
		public static function auth($uUsername, $uPassword) {
			foreach(config::get(config::MAIN, '/users/userList', array()) as $tUser) {
				if($uUsername != $tUser['username'] || md5($uPassword) != $tUser['password']) {
					continue;
				}

				session::set('user', $tUser);
				return true;
			}

			session::remove('user');
			return false;
		}

		/**
		* @ignore
		*/
		public static function check($uRequiredRoles = 'user') {
			$tUser = session::get('user');
			if(is_null($tUser)) {
				return false;
			}
		
			$tAvailableRoles = explode(',', $tUser['roles']);

			foreach(explode(',', $uRequiredRoles) as $tRequiredRole) {
				if(!in_array($tRequiredRole, $tAvailableRoles, true)) {
					return false;
				}
			}

			return true;
		}
	}

?>