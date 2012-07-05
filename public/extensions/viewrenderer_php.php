<?php

if(extensions::isSelected('viewrenderer_php')) {
	/**
	* ViewRenderer: PHP Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class viewrenderer_php {
		public static $extension;
		public static $templatePath;

		public static function extension_info() {
			return array(
				'name' => 'viewrenderer: php',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		public static function extension_load() {
			events::register('renderview', events::Callback('viewrenderer_php::renderview'));

			self::$extension = config::get('/php/templates/@extension', '.php');
			self::$templatePath = framework::translatePath(config::get('/php/templates/@templatePath', '{app}views'));
		}

		public static function renderview($uObject) {
			if($uObject['viewExtension'] != self::$extension) {
				return;
			}

			$tInputFile = self::$templatePath . '/' . $uObject['viewFile'];

			// variable extraction
			$model = &$uObject['model'];
			if(is_array($model)) {
				extract($model, EXTR_SKIP|EXTR_REFS);
			}

			extract($uObject['extra'], EXTR_SKIP|EXTR_REFS);

			require($tInputFile);
		}
	}
}

?>