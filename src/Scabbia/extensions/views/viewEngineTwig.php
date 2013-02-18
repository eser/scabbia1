<?php

	namespace Scabbia\Extensions\Views;

	/**
	 * ViewEngine: Twig Extension
	 *
	 * @package Scabbia
	 * @subpackage viewEngineTwig
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends mvc
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class viewEngineTwig {
		/**
		 * @ignore
		 */
		public static $loader = null;
		/**
		 * @ignore
		 */
		public static $engine = null;

		/**
		 * @ignore
		 */
		public static function extensionLoad() {
			views::registerViewEngine('twig', 'viewEngineTwig');
		}

		/**
		 * @ignore
		 */
		public static function renderview($uObject) {
			if(is_null(self::$engine)) {
				$tPath = framework::translatePath(config::get('/twig/path', '{core}include/3rdparty/twig/lib/Twig'));
				require($tPath . '/Autoloader.php');

				Twig_Autoloader::register();
				self::$loader = new \Twig_Loader_Filesystem($uObject['templatePath']);

				$tOptions = array(
					'cache' => framework::writablePath('cache/twig/')
				);

				if(framework::$development >= 1) {
					$tOptions['auto_reload'] = true;
				}

				self::$engine = new \Twig_Environment(self::$loader, $tOptions);
			}

			$model = array('model' => &$uObject['model']);

			if(is_array($uObject['model'])) {
				$model = array_merge($model, $uObject['model']);
			}

			if(isset($uObject['extra'])) {
				$model = array_merge($model, $uObject['extra']);
			}

			echo self::$engine->render($uObject['templateFile'], $model);
		}
	}

	?>