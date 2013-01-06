<?php

	namespace Scabbia;

	/**
	 * Controller Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	abstract class controller {
		/**
		 * @ignore
		 */
		public $view = null;
		/**
		 * @ignore
		 */
		public $db;
		/**
		 * @ignore
		 */
		public $vars = array();

		/**
		 * @ignore
		 */
		public function __construct() {
			if(extensions::isLoaded('database')) {
				$this->db = database::get(); // default database to member 'db'
			}
		}

		/**
		 * @ignore
		 */
		public function mapDirectory($uDirectory, $uExtension, $uAction, $uArgs) {
			$tMap = io::mapFlatten(framework::translatePath($uDirectory), '*' . $uExtension, true, true);

			array_unshift($uArgs, $uAction);
			$tPath = implode('/', $uArgs);

			if(in_array($tPath, $tMap, true)) {
				$this->view($uDirectory . $tPath . $uExtension);

				return true;
			}

			return false;
		}

		/**
		 * @ignore
		 */
		public function render($uAction, $uArgs) {
			$tActionMethodName = strtr($uAction, '/', '_');

			$tMe = new \ReflectionClass($this);

			while(true) {
				$tMethod = http::$methodext . '_' . $tActionMethodName;
				if($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
					break;
				}

				// fallback
				$tMethod = http::$method . '_' . $tActionMethodName;
				if($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
					break;
				}

				if($tMe->hasMethod($tActionMethodName) && $tMe->getMethod($tActionMethodName)->isPublic()) {
					$tMethod = $tActionMethodName;
					break;
				}

				return false;
			}

			return call_user_func_array(array(&$this, $tMethod), $uArgs);
		}

		/**
		 * @ignore
		 */
		public function get($uKey) {
			return $this->vars[$uKey];
		}

		/**
		 * @ignore
		 */
		public function set($uKey, $uValue) {
			$this->vars[$uKey] = $uValue;
		}

		/**
		 * @ignore
		 */
		public function setRef($uKey, &$uValue) {
			$this->vars[$uKey] = $uValue;
		}

		/**
		 * @ignore
		 */
		public function remove($uKey) {
			unset($this->vars[$uKey]);
		}

		/**
		 * @ignore
		 */
		public function loadDatabase($uDatabaseName, $uMemberName = null) {
			$uArgs = func_get_args();

			if(is_null($uMemberName)) {
				$uMemberName = $uDatabaseName;
			}

			$this->{$uMemberName} = call_user_func_array('Scabbia\\controllers::loadDatabase', $uArgs);
		}

		/**
		 * @ignore
		 */
		public function load($uModelClass, $uMemberName = null) {
			$uArgs = func_get_args();

			if(is_null($uMemberName)) {
				$uMemberName = $uModelClass;
			}

			$this->{$uMemberName} = call_user_func_array('Scabbia\\controllers::load', $uArgs);
		}

		/**
		 * @ignore
		 */
		public function view($uView = null, $uModel = null) {
			views::view(
				!is_null($uView) ? $uView : $this->view,
				!is_null($uModel) ? $uModel : $this->vars
			);
		}

		/**
		 * @ignore
		 */
		public function viewFile($uView = null, $uModel = null) {
			views::viewFile(
				!is_null($uView) ? $uView : $this->view,
				!is_null($uModel) ? $uModel : $this->vars
			);
		}

		/**
		 * @ignore
		 */
		public function json($uModel = null) {
			views::json(
				!is_null($uModel) ? $uModel : $this->vars
			);
		}

		/**
		 * @ignore
		 */
		public function xml($uModel = null) {
			views::xml(
				!is_null($uModel) ? $uModel : $this->vars
			);
		}

		/**
		 * @ignore
		 */
		public function redirect() {
			$uArgs = func_get_args();
			call_user_func_array('Scabbia\\mvc::redirect', $uArgs);
		}

		/**
		 * @ignore
		 */
		public function end() {
			$uArgs = func_get_args();
			call_user_func_array('Scabbia\\framework::end', $uArgs);
		}
	}

?>