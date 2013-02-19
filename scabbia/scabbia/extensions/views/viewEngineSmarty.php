<?php

	namespace Scabbia\Extensions\Views;

	use Scabbia\Extensions\Views\views;
	use Scabbia\framework;
	use Scabbia\config;

	/**
	 * ViewEngine: Smarty Extension
	 *
	 * @package Scabbia
	 * @subpackage viewEngineSmarty
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends mvc
	 * @scabbia-phpversion 5.3.0
	 * @scabbia-phpdepends
	 */
	class viewEngineSmarty {
		/**
		 * @ignore
		 */
		public static $engine = null;

		/**
		 * @ignore
		 */
		public static function extensionLoad() {
			views::registerViewEngine('tpl', 'viewEngineSmarty');
		}

		/**
		 * @ignore
		 */
		public static function renderview($uObject) {
			if(is_null(self::$engine)) {
				$tPath = framework::translatePath(config::get('/smarty/path', '{vendor}scabbia/scabbia/include/3rdparty/smarty/libs'));
				require($tPath . '/Smarty.class.php');

				self::$engine = new \Smarty();

				self::$engine->setTemplateDir($uObject['templatePath']);
				self::$engine->setCompileDir(framework::writablePath('cache/smarty/'));

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