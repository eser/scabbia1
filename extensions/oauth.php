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
		public static function extension_info() {
			return array(
				'name' => 'oauth',
				'version' => '1.0.2',
				'phpversion' => '5.2.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		/**
		* @ignore
		*/
		public function index() {
			$this->view('{core}views/oauth/index.php');
		}
	}

?>