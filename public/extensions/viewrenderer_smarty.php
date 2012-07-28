<?php

if(extensions::isSelected('viewrenderer_smarty')) {
	/**
	* ViewRenderer: Smarty Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class viewrenderer_smarty {
		public static $renderer = null;
		public static $extension;
		public static $templatePath;
		public static $compiledPath;

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
			events::register('renderview', events::Callback('viewrenderer_smarty::renderview'));

			self::$extension = config::get('/smarty/templates/@extension', '.tpl');
			self::$templatePath = framework::translatePath(config::get('/smarty/templates/@templatePath', '{app}views'));
			self::$compiledPath = framework::translatePath(config::get('/smarty/templates/@compiledPath', '{app}writable/compiledViews'));
		}

		public static function renderview($uObject) {
			if($uObject['viewExtension'] != self::$extension) {
				return;
			}

			if(is_null(self::$renderer)) {
				$tPath = framework::translatePath(config::get('/smarty/installation/@path', '{core}include/3rdparty/smarty/libs'));
				require($tPath . '/Smarty.class.php');

				self::$renderer = new Smarty();

				self::$renderer->setTemplateDir(self::$templatePath . '/');
				self::$renderer->setCompileDir(self::$compiledPath . '/');

				if(framework::$development) {
					self::$renderer->force_compile = true;
				}
			}
			else {
				self::$renderer->clearAllAssign();
			}

			self::$renderer->assignByRef('model', $uObject['model']);
			if(is_array($uObject['model'])) {
				foreach($uObject['model'] as $tKey => &$tValue) {
					self::$renderer->assignByRef($tKey, $tValue);
				}
			}

			if(isset($uObject['extra'])) {
				foreach($uObject['extra'] as $tKey => &$tValue) {
					self::$renderer->assignByRef($tKey, $tValue);
				}
			}

			self::$renderer->display($uObject['viewFile']);
		}
	}
}

?>