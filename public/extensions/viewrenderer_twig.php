<?php

if(Extensions::isSelected('viewrenderer_twig')) {
	class viewrenderer_twig {
		public static $loader = null;
		public static $renderer = null;
		public static $extension;
		public static $templatePath;
		public static $compiledPath;

		public static function extension_info() {
			return array(
				'name' => 'viewrenderer: twig',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		public static function extension_load() {
			Events::register('renderview', Events::Callback('viewrenderer_twig::renderview'));

			self::$extension = Config::get('/twig/templates/@extension', '.twig');
			self::$templatePath = QPATH_APP . Config::get('/twig/templates/@templatePath', 'views');
			self::$compiledPath = QPATH_APP . Config::get('/twig/templates/@compiledPath', 'views/compiled');
		}

		public static function renderview($uObject) {
			if($uObject['viewExtension'] != self::$extension) {
				return;
			}

			if(is_null(self::$renderer)) {
				$tPath = Config::get('/twig/installation/@path', 'include/3rdparty/twig/lib/Twig');
				require($tPath . '/Autoloader.php');

				Twig_Autoloader::register();
				self::$loader = new Twig_Loader_Filesystem(self::$templatePath);
				self::$renderer = new Twig_Environment(self::$loader, array(
					'cache' => self::$compiledPath
				));
			}
			
			echo self::$renderer->render($uObject['viewFile'], array_combine($uObject['model'], $uObject['extra']));
		}
	}
}

?>