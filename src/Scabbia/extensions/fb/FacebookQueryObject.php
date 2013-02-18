<?php

	namespace Scabbia;

	/**
	 * Facebook Query Object Class
	 *
	 * @package Scabbia
	 * @subpackage ExtensibilityExtensions
	 */
	class FacebookQueryObject {
		public $object;
		public $data;
		public $hasPreviousPage;
		public $hasNextPage;

		/**
		 * @param $uObject
		 */
		public function __construct($uObject) {
			$this->object = $uObject;
			$this->data = (isset($this->object['data']) ? $this->object['data'] : null);
			$this->hasPreviousPage = (isset($this->object['paging']) && isset($this->object['paging']['previous']));
			$this->hasNextPage = (isset($this->object['paging']) && isset($this->object['paging']['next']));
		}
	}

	?>