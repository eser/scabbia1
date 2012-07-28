<?php

if(extensions::isSelected('viewrenderer_raintpl')) {
	/**
	* ViewRenderer: RainTpl Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class viewrenderer_raintpl {
		public static $renderer = null;
		public static $extension;
		public static $templatePath;
		public static $compiledPath;

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
			events::register('renderview', events::Callback('viewrenderer_raintpl::renderview'));

			self::$extension = config::get('/raintpl/templates/@extension', '.rain');
			self::$templatePath = framework::translatePath(config::get('/raintpl/templates/@templatePath', '{app}views'));
			self::$compiledPath = framework::translatePath(config::get('/raintpl/templates/@compiledPath', '{app}writable/compiledViews'));
		}

		public static function renderview($uObject) {
			if($uObject['viewExtension'] != self::$extension) {
				return;
			}

			if(is_null(self::$renderer)) {
				$tPath = framework::translatePath(config::get('/raintpl/installation/@path', '{core}include/3rdparty/raintpl/inc'));
				require($tPath . '/rain.tpl.class.php');

				raintpl::configure('base_url', null);
				raintpl::configure('tpl_dir', self::$templatePath . '/');
				raintpl::configure('tpl_ext', self::$extension);
				raintpl::configure('cache_dir', self::$compiledPath . '/');

				if(framework::$development) {
					raintpl::configure('check_template_update', true);
				}

				self::$renderer = new RainTPL();
			}
			else {
				self::$renderer = new RainTPL();
			}

			self::$renderer->assign('model', $uObject['model']);
			if(is_array($uObject['model'])) {
				foreach($uObject['model'] as $tKey => &$tValue) {
					self::$renderer->assign($tKey, $tValue);
				}
			}

			if(isset($uObject['extra'])) {
				foreach($uObject['extra'] as $tKey => &$tValue) {
					self::$renderer->assign($tKey, $tValue);
				}
			}

			self::$renderer->draw($uObject['viewFile']);
		}
	}
}

?>