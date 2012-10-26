<?php

	/**
	* Auth Extension
	*
	* @package Scabbia
	* @subpackage auth
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends session
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class auth {
		/**
		* @ignore
		*/
		public static function extension_load() {
		}

		/**
		* @ignore
		*/
		public static function login($uUsername, $uPassword) {
			foreach(config::get(config::MAIN, '/auth/userList', array()) as $tUser) {
				if($uUsername != $tUser['username'] || md5($uPassword) != $tUser['password']) {
					continue;
				}

				session::set('user', $tUser);
				return true;
			}

			// session::remove('user');
			return false;
		}

		/**
		* @ignore
		*/
		public static function clear() {
			session::remove('user');
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
		
		/**
		* @ignore
		*/
		public static function checkRedirect($uRequiredRoles = 'user') {
			if(self::check($uRequiredRoles)) {
				return;
			}

			$tMvcUrl = config::get(config::MAIN, '/auth/loginMvcUrl', null);
			if(!is_null($tMvcUrl) && extensions::isLoaded('mvc')) {
				//! todo: warning messages like insufficent privileges.
				mvc::redirect($tMvcUrl);
			}
			else {
				header('Location: ' . config::get(config::MAIN, '/auth/loginUrl'));
			}

			framework::end(0);
		}
	}

?>