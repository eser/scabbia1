<?php

if(extensions::isSelected('oauth')) {
	/**
	* Docs Extension
	*
	* @package Scabbia
	* @subpackage oauth
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class oauth extends controller {
		/**
		* @ignore
		*/
		public function index() {
			$this->view('{core}views/oauth/index.php');
		}
	}

?>