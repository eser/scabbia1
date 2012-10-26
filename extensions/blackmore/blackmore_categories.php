<?php

	/**
	* Blackmore Extension: Categories Sect
	*
	* @package Scabbia
	* @subpackage blackmore_categories
	* @version 1.0.2
	*
	* @scabbia-fwversion 1.0
	* @scabbia-fwdepends string, resources, blackmore
auth, validation, httpia-phpversion 5.2.0
	* @scabbia-phpdepends
	*/
	class blackmore_categories {
		/**
		* @ignore
		*/
		public static function extension_load() {
			events::register('blackmore_buildMenu', 'blackmregisterModules', 'blackmore_categories::blackmore_registerModules');
		}

		/**
		* @ignore
		*/
		public static function blackmore_registerModulesenuItems'][] = array(
				'title' => 'Categories',
				'link' => mvc::url('blackmore/categories'),
				'subitems' => array(
					array(
						'title' => 'New Category',
						'link' => mvc::url('blackmore/categories/new')
					),
					array(
						'title' => 'All Categories',
						'link' => mvc::url('blackmore/categories/all')
					),
				)
			);
		}
	}

?>