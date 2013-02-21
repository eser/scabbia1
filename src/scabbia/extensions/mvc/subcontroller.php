<?php

	namespace Scabbia\Extensions\Mvc;

	use Scabbia\Extensions\Http\http;

	/**
	 * Subcontroller Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class subcontroller {
		/**
		 * @ignore
		 */
		public $subcontrollers = array();
		/**
		 * @ignore
		 */
		public $defaultAction = 'index';
		/**
		 * @ignore
		 */
		public $vars = array();


		/**
		 * @ignore
		 */
		public function render($uAction, $uParams) {
			$tActionName = strtr($uAction, '/', '_');

			if(isset($this->subcontrollers[$tActionName])) {
				if(count($uParams) > 0) {
					$tSubaction = array_shift($uParams);
				}
				else {
					$tSubaction = $this->subcontrollers[$tActionName]->defaultAction;
				}

				$tInstance = new $this->subcontrollers[$tActionName] ();
				return $tInstance->render($tSubaction, $uParams);
			}

			$tMe = new \ReflectionClass($this);

			while(true) {
				$tMethod = http::$methodext . '_' . $tActionName;
				if($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
					break;
				}

				// fallback
				$tMethod = http::$method . '_' . $tActionName;
				if($tMe->hasMethod($tMethod) && $tMe->getMethod($tMethod)->isPublic()) {
					break;
				}

				// fallback 2
				if($tMe->hasMethod($tActionName) && $tMe->getMethod($tActionName)->isPublic()) {
					$tMethod = $tActionName;
					break;
				}

				return false;
			}

			array_push(controllers::$stack, $this);
			$tReturn = call_user_func_array(array(&$this, $tMethod), $uParams);
			array_pop(controllers::$stack);

			return $tReturn;
		}

		/**
		 * @ignore
		 */
		public function addSubcontroller($uAction, $uClass) {
			$this->subcontrollers[$uAction] = $uClass;
		}

		/**
		 * @ignore
		 */
		public function export() {
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
		public static function setRange($uArray) {
			foreach($uArray as $tKey => $tValue) {
				$this->vars[$tKey] = $tValue;
			}
		}

		/**
		 * @ignore
		 */
		public function remove($uKey) {
			unset($this->vars[$uKey]);
		}
	}

	?>