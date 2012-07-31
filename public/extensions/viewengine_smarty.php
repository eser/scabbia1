<?php

if(extensions::isSelected('viewengine_smarty')) {
	/**
	* ViewEngine: Smarty Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class viewengine_smarty {
		public static $engine = null;

		public static function extension_info() {
			return array(
				'name' => 'viewengine: smarty',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('mvc')
			);
		}

		public static function extension_load() {
			mvc::registerViewEngine('tpl', 'viewengine_smarty');
		}

		public static function renderview($uObject) {
			if(is_null(self::$engine)) {
				$tPath = framework::translatePath(config::get('/smarty/installation/@path', '{core}include/3rdparty/smarty/libs'));
				require($tPath . '/Smarty.class.php');

				self::$engine = new Smarty();

				self::$engine->setTemplateDir($uObject['templatePath'] . '/');
				self::$engine->setCompileDir($uObject['compiledPath'] . '/');

				if(framework::$development) {
					self::$engine->force_compile = true;
				}
			}
			else {
				self::$engine->clearAllAssign();
			}

			self::$engine->assignByRef('model', $uObject['model']);
			if(is_array($uObject['model'])) {
				foreach($uObject['model'] as $tKey => &$tValue) {
					self::$engine->assignByRef($tKey, $tValue);
				}
			}

			if(isset($uObject['extra'])) {
				foreach($uObject['extra'] as $tKey => &$tValue) {
					self::$engine->assignByRef($tKey, $tValue);
				}
			}

			self::$engine->display($uObject['viewFile']);
		}
	}
}

?>