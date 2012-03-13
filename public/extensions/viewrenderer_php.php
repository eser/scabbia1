<?php

if(Extensions::isSelected('viewrenderer_php')) {
	class viewrenderer_php {
		private static $extension;
		private static $templatePath;

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
			Events::register('renderview', Events::Callback('viewrenderer_php::renderview'));

			self::$extension = Config::get('/php/templates/@extension', 'php');
			self::$templatePath = QPATH_APP . Config::get('/php/templates/@templatePath', 'views');
		}

		public static function renderview($uObject) {
			if($uObject['viewExtension'] != self::$extension) {
				return;
			}

			$tInputFile = self::$templatePath . '/' . $uObject['viewFile'] . '.' . $uObject['viewExtension'];

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