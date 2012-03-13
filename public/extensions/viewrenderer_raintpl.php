<?php

if(Extensions::isSelected('viewrenderer_raintpl')) {
	class viewrenderer_raintpl {
		private static $renderer = null;
		private static $extension;
		private static $templatePath;
		private static $compiledPath;

		public static function extension_info() {
			return array(
				'name' => 'viewrenderer: raintpl',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		public static function extension_load() {
			Events::register('renderview', Events::Callback('viewrenderer_raintpl::renderview'));

			self::$extension = Config::get('/raintpl/templates/@extension', 'rain');
			self::$templatePath = QPATH_APP . Config::get('/raintpl/templates/@templatePath', 'views');
			self::$compiledPath = QPATH_APP . Config::get('/raintpl/templates/@compiledPath', 'views/compiled');
		}

		public static function renderview($uObject) {
			if($uObject['viewExtension'] != self::$extension) {
				return;
			}

			if(is_null(self::$renderer)) {
				$tPath = Config::get('/raintpl/installation/@path', 'include/3rdparty/raintpl/inc');
				require($tPath . '/rain.tpl.class.php');

				raintpl::configure('base_url', null);
				raintpl::configure('tpl_dir', self::$templatePath . '/');
				raintpl::configure('tpl_ext', self::$extension);
				raintpl::configure('cache_dir', self::$compiledPath . '/');

				self::$renderer = new RainTPL();
			}
			else {
				self::$renderer = new RainTPL();
			}

			if(is_array($uObject['model'])) {
				foreach($uObject['model'] as $tKey => &$tValue) {
					self::$renderer->assign($tKey, $tValue);
				}
			}
			else {
				self::$renderer->assign('model', $uObject['model']);
			}

			foreach($uObject['extra'] as $tKey => &$tValue) {
				self::$renderer->assign($tKey, $tValue);
			}

			self::$renderer->draw($uObject['viewFile']); //  . '.' . $uObject['viewExtension']
		}
	}
}

?>