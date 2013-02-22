<?php

	namespace Scabbia\Extensions\Mvc;

	use Scabbia\Extensions\Datasources\datasources;
	use Scabbia\Extensions\Http\http;
	use Scabbia\Extensions\Io\io;
	use Scabbia\Extensions\Mvc\subcontroller;
	use Scabbia\Extensions\Views\views;
	use Scabbia\extensions;
	use Scabbia\framework;

	/**
	 * Controller Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	abstract class controller extends subcontroller {
		/**
		 * @ignore
		 */
		public $route = null;
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
		public function __construct() {
			$this->db = datasources::get(); // default datasource to member 'db'
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
		public function loadDatasource($uDatasourceName, $uMemberName = null) {
			$uArgs = func_get_args();

			if(is_null($uMemberName)) {
				$uMemberName = $uDatasourceName;
			}

			$this->{$uMemberName} = call_user_func_array('Scabbia\\Extensions\\Mvc\\controllers::loadDatasource', $uArgs);
		}

		/**
		 * @ignore
		 */
		public function load($uModelClass, $uMemberName = null) {
			$uArgs = func_get_args();

			if(is_null($uMemberName)) {
				$uMemberName = $uModelClass;
			}

			$this->{$uMemberName} = call_user_func_array('Scabbia\\Extensions\\Mvc\\controllers::load', $uArgs);
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
			call_user_func_array('Scabbia\\Extensions\\Mvc\\mvc::redirect', $uArgs);
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