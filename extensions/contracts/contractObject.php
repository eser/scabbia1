<?php

	namespace Scabbia;

	/**
	 * Contract Object Class
	 *
	 * @package Scabbia
	 * @subpackage ExtensibilityExtensions
	 */
	class contractObject {
		/**
		 * @ignore
		 */
		public $status;
		/**
		 * @ignore
		 */
		public $newValue;

		/**
		 * @ignore
		 */
		public function __construct($uStatus, $uNewValue = null) {
			$this->status = $uStatus;
			$this->newValue = $uNewValue;
		}

		/**
		 * @ignore
		 */
		public function exception($uErrorMessage) {
			if($this->status) {
				return;
			}

			throw new \Exception($uErrorMessage);
		}

		/**
		 * @ignore
		 */
		public function check() {
			return $this->status;
		}

		/**
		 * @ignore
		 */
		public function get() {
			if(!$this->status) {
				return false;
			}

			return $this->newValue;
		}
	}

?>