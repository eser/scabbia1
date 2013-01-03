<?php

	/**
	 * ViewEngine: RainTpl Extension
	 *
	 * @package Scabbia
	 * @subpackage viewengine_raintpl
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends mvc
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class viewengine_raintpl {
		/**
		 * @ignore
		 */
		public static $engine = null;

		/**
		 * @ignore
		 */
		public static function extension_load() {
			views::registerViewEngine('rain', 'viewengine_raintpl');
		}

		/**
		 * @ignore
		 */
		public static function renderview($uObject) {
			if(is_null(self::$engine)) {
				$tPath = framework::translatePath(config::get('/raintpl/path', '{core}include/3rdparty/raintpl/inc'));
				require($tPath . '/rain.tpl.class.php');

				raintpl::configure('base_url', null);
				raintpl::configure('tpl_dir', $uObject['templatePath']);
				raintpl::configure('tpl_ext', '.rain');
				raintpl::configure('cache_dir', $uObject['compiledPath']);

				if(framework::$development >= 1) {
					raintpl::configure('check_template_update', true);
				}

				self::$engine = new RainTPL();
			}
			else {
				self::$engine = new RainTPL();
			}

			self::$engine->assign('model', $uObject['model']);
			if(is_array($uObject['model'])) {
				foreach($uObject['model'] as $tKey => $tValue) {
					self::$engine->assign($tKey, $tValue);
				}
			}

			if(isset($uObject['extra'])) {
				foreach($uObject['extra'] as $tKey => $tValue) {
					self::$engine->assign($tKey, $tValue);
				}
			}

			self::$engine->draw($uObject['templateFile']);
		}
	}

?>