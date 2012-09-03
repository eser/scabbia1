<?php

	class oauth extends controller {
		/**
		* @ignore
		*/
		public function __construct() {
			if(framework::$development <= 0) {
				exit('why?');
			}
		}

		/**
		* @ignore
		*/
		public function index() {
			$this->view('{core}views/oauth/index.php');
		}
	}

?>