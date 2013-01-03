<?php

	/**
	 * ViewEngine: Smarty Extension
	 *
	 * @package Scabbia
	 * @subpackage viewengine_smarty
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends mvc
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class viewengine_smarty {
		/**
		 * @ignore
		 */
		public static $engine = null;

		/**
		 * @ignore
		 */
		public static function extension_load() {
			views::registerViewEngine('tpl', 'viewengine_smarty');
		}

		/**
		 * @ignore
		 */
		public static function renderview($uObject) {
			if(is_null(self::$engine)) {
				$tPath = framework::translatePath(config::get('/smarty/path', '{core}include/3rdparty/smarty/libs'));
				require($tPath . '/Smarty.class.php');

				self::$engine = new Smarty();

				self::$engine->setTemplateDir($uObject['templatePath']);
				self::$engine->setCompileDir($uObject['compiledPath']);

				if(framework::$development >= 1) {
					self::$engine->force_compile = true;
				}
			}
			else {
				self::$engine->clearAllAssign();
			}

			self::$engine->assignByRef('model', $uObject['model']);
			if(is_array($uObject['model'])) {
				foreach($uObject['model'] as $tKey => $tValue) {
					self::$engine->assignByRef($tKey, $tValue);
				}
			}

			if(isset($uObject['extra'])) {
				foreach($uObject['extra'] as $tKey => $tValue) {
					self::$engine->assignByRef($tKey, $tValue);
				}
			}

			self::$engine->display($uObject['templateFile']);
		}
	}

?>