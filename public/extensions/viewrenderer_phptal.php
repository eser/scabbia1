<?php

if(extensions::isSelected('viewrenderer_phptal')) {
	/**
	* ViewRenderer: PHPTAL Extension
	*
	* @package Scabbia
	* @subpackage Extensions
	*/
	class viewrenderer_phptal {
		public static $renderer = null;
		public static $extension;
		public static $templatePath;
		public static $compiledPath;

		public static function extension_info() {
			return array(
				'name' => 'viewrenderer: phptal',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array()
			);
		}

		public static function extension_load() {
			events::register('renderview', events::Callback('viewrenderer_phptal::renderview'));

			self::$extension = config::get('/phptal/templates/@extension', '.zpt');
			self::$templatePath = framework::translatePath(config::get('/phptal/templates/@templatePath', '{app}views'));
			self::$compiledPath = framework::translatePath(config::get('/phptal/templates/@compiledPath', '{app}writable/compiledViews'));
		}

		public static function renderview($uObject) {
			if($uObject['viewExtension'] != self::$extension) {
				return;
			}

			if(is_null(self::$renderer)) {
				$tPath = framework::translatePath(config::get('/phptal/installation/@path', '{core}include/3rdparty/PHPTAL'));
				require($tPath . '/PHPTAL.php');

				self::$renderer = new PHPTAL();
			}
			else {
				unset(self::$renderer);

				// I just don't want to do it in this way,
				// but phptal.org documentation says it so.
				self::$renderer = new PHPTAL();
			}

			if(is_array($uObject['model'])) {
				foreach($uObject['model'] as $tKey => &$tValue) {
					self::$renderer->set($tKey, $tValue);
				}
			}
			else {
				self::$renderer->set('model', $uObject['model']);
			}

			foreach($uObject['extra'] as $tKey => &$tValue) {
				self::$renderer->set($tKey, $tValue);
			}

			self::$renderer->setForceReparse(false);
			self::$renderer->setTemplateRepository(self::$templatePath . '/');
			self::$renderer->setPhpCodeDestination(self::$compiledPath . '/');
			self::$renderer->setOutputMode(PHPTAL::HTML5);
			self::$renderer->setEncoding('UTF-8');
			self::$renderer->setTemplate($uObject['viewFile']);
			if(framework::$development) {
				self::$renderer->prepare();
			}

			self::$renderer->echoExecute();
		}
	}
}

?>