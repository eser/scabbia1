<?php

if(Extensions::isSelected('viewrenderer_smarty')) {
	class viewrenderer_smarty {
		private static $renderer = null;
		private static $extension;
		private static $templatePath;
		private static $compiledPath;

		public static function extension_info() {
			return array(
				'name' => 'viewrenderer: smarty',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		public static function extension_load() {
			Events::register('renderview', Events::Callback('viewrenderer_smarty::renderview'));

			self::$extension = Config::get('/smarty/templates/@extension', 'tpl');
			self::$templatePath = QPATH_APP . Config::get('/smarty/templates/@templatePath', 'views');
			self::$compiledPath = QPATH_APP . Config::get('/smarty/templates/@compiledPath', 'views/compiled');
		}

		public static function renderview($uObject) {
			if($uObject['viewExtension'] != self::$extension) {
				return;
			}

			if(is_null(self::$renderer)) {
				$tPath = Config::get('/smarty/installation/@path', 'include/3rdparty/smarty/libs');
				require($tPath . '/Smarty.class.php');

				self::$renderer = new Smarty();

				self::$renderer->setTemplateDir(self::$templatePath . '/');
				self::$renderer->setCompileDir(self::$compiledPath . '/');
			}
			else {
				self::$renderer->clearAllAssign();
			}

			if(is_array($uObject['model'])) {
				foreach($uObject['model'] as $tKey => &$tValue) {
					self::$renderer->assignByRef($tKey, $tValue);
				}
			}
			else {
				self::$renderer->assignByRef('model', $uObject['model']);
			}

			foreach($uObject['extra'] as $tKey => &$tValue) {
				self::$renderer->assignByRef($tKey, $tValue);
			}

			self::$renderer->display($uObject['viewFile'] . '.' . $uObject['viewExtension']);
		}
	}
}

?>