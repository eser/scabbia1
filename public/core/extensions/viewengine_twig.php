<?php

if(extensions::isSelected('viewengine_twig')) {
	/**
	* ViewEngine: Twig Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class viewengine_twig {
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
		public static function extension_info() {
			return array(
				'name' => 'viewengine: twig',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('mvc')
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
			mvc::registerViewEngine('twig', 'viewengine_twig');
		}

		/**
		* @ignore
		*/
		public static function renderview($uObject) {
			if(is_null(self::$engine)) {
				$tPath = framework::translatePath(config::get('/twig/installation/@path', '{core}include/3rdparty/twig/lib/Twig'));
				require($tPath . '/Autoloader.php');

				Twig_Autoloader::register();
				self::$loader = new Twig_Loader_Filesystem($uObject['templatePath']);

				$tOptions = array(
					'cache' => $uObject['compiledPath']
				);

				if(framework::$development >= 1) {
					$tOptions['auto_reload'] = true;
				}

				self::$engine = new Twig_Environment(self::$loader, $tOptions);
			}

			$model = array('model' => &$uObject['model']);

			if(is_array($uObject['model'])) {
				$model = array_merge($model, $uObject['model']);
			}

			if(isset($uObject['extra'])) {
				$model = array_merge($model, $uObject['extra']);
			}

			echo self::$engine->render($uObject['viewFile'], $model);
		}
	}
}

?>