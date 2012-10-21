<?php

if(extensions::isSelected('docs')) {
	/**
	* Docs Extension
	*
	* @package Scabbia
	* @subpackage docs
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends
	* @scabbia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class docs extends controller {
		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'docs',
				'version' => '1.0.2',
				'phpversion' => '5.2.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('resources', 'viewengine_markdown')
			);
		}
	}
}

?>