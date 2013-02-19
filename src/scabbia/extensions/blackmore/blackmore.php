<?php

	namespace Scabbia\Extensions\Blackmore;

	use Scabbia\events;
	use Scabbia\Extensions\Http\http;
	use Scabbia\Extensions\Auth\auth;
	use Scabbia\Extensions\Validation\validation;
	use Scabbia\Extensions\Controllers\controller;

	/**
	 * Blackmore Extension
	 *
	 * @package Scabbia
	 * @subpackage blackmore
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string, resources, validation, http, auth, zmodels
	 * @scabbia-phpversion 5.3.0
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
		public static $module;

		/**
		 * @ignore
		 */
		public function render($uAction, $uArgs) {
			self::$modules['index'] = array(
				'title' => 'Dashboard',
				'callback' => array(&$this, 'index')
			);

			$tParms = array(
				'modules' => &self::$modules
			);
			events::invoke('blackmoreRegisterModules', $tParms);

			self::$modules['login'] = array(
				'title' => 'Logout',
				'callback' => array(&$this, 'login')
			);

			if(!isset(self::$modules[$uAction])) {
				return false;
			}

			self::$module = $uAction;

			if(count($uArgs) > 0) {
				foreach(self::$modules[$uAction]['actions'] as $tAction) {
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

				$this->viewFile('{vendor}views/blackmore/login.php');

				return;
			}

			// validations
			validation::addRule('username')->isRequired()->errorMessage('Username shouldn\'t be blank.');
			// validation::addRule('username')->isEmail()->errorMessage('Please consider your e-mail address once again.');
			validation::addRule('password')->isRequired()->errorMessage('Password shouldn\'t be blank.');
			validation::addRule('password')->lengthMinimum(4)->errorMessage('Password should be longer than 4 characters at least.');

			if(!validation::validate($_POST)) {
				$this->set('error', implode('<br />', validation::getErrorMessages(true)));
				$this->viewFile('{vendor}views/blackmore/login.php');

				return;
			}

			$username = http::post('username');
			$password = http::post('password');

			// user not found
			if(!auth::login($username, $password)) {
				$this->set('error', 'User not found');
				$this->viewFile('{vendor}views/blackmore/login.php');

				return;
			}

			$this->redirect('blackmore/index');
		}

		/**
		 * @ignore
		 */
		public function index() {
			auth::checkRedirect('user');

			$this->viewFile('{vendor}views/blackmore/index.php');
		}
	}

	?>