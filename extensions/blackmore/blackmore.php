<?php

	/**
	* Blackmore Extension
	*
	* @package Scabbia
	* @subpackage blackmore
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends string, resources, validation, http, auth, zmodels
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class blackmore extends controller {
		/**
		* @ignore
		*/
		public static $menuItems = array();
		/**
		* @ignore
		*/
		public static $modules = array();

		/**
		* @ignore
		*/
		public function render(&$uAction, &$uArgs) {
			self::$modules['index'] = array(
				'title' => 'Dashboard',
				'callback' => array(&$this, 'index')
			);

			events::invoke('blackmore_registerModules', array(
				'modules' => &self::$modules
			));

			self::$modules['login'] = array(
				'title' => 'Logout',
				'callback' => array(&$this, 'login')
			);

			if(!isset(self::$modules[$uAction])) {
				return false;
			}

			if(count($uArgs) > 0) {
				foreach(self::$modules[$uAction]['actions'] as &$tAction) {
					if($uArgs[0] != $tAction['action']) {
						continue;
					}

					return call_user_func_array($tAction['callback'], $uArgs);
				}
			}

			return call_user_func_array(self::$modules[$uAction]['callback'], $uArgs);
		}

		/**
		* @ignore
		*/
		public function login() {
			if(http::$method != 'post') {
				auth::clear();

				$this->viewFile('{core}views/blackmore/login.php');
				return;
			}

			// validations
			validation::addRule('username')->isRequired()->errorMessage('Username shouldn\'t be blank.');
			// validation::addRule('username')->isEmail()->errorMessage('Please consider your e-mail address once again.');
			validation::addRule('password')->isRequired()->errorMessage('Password shouldn\'t be blank.');
			validation::addRule('password')->lengthMinimum(4)->errorMessage('Password should be longer than 4 characters at least.');

			if(!validation::validate($_POST)) {
				$this->set('error', implode('<br />', validation::getErrorMessages(true)));
				$this->viewFile('{core}views/blackmore/login.php');

				return;
			}

			$username = http::post('username');
			$password = http::post('password');

			// user not found
			if(!auth::login($username, $password)) {
				$this->set('error', 'User not found');
				$this->viewFile('{core}views/blackmore/login.php');

				return;
			}

			$this->redirect('blackmore/index');
		}

		/**
		* @ignore
		*/
		public function index() {
			auth::checkRedirect('user');

			$this->viewFile('{core}views/blackmore/index.php');
		}
	}

?>