<?php

	namespace Scabbia\Extensions\Views;

	/**
	 * View Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class view {
		/**
		 * @ignore
		 */
		public $path;
		/**
		 * @ignore
		 */
		public $vars;

		/**
		 * @ignore
		 */
		public function __construct($uPath) {
			$this->path = $uPath;
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
		public function setRange($uArray) {
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

		/**
		 * @ignore
		 */
		public function render() {
			views::view($this->path, $this->vars);
		}

		/**
		 * @ignore
		 */
		public function renderFile() {
			views::viewFile($this->path, $this->vars);
		}
	}

	?>