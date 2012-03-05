<?php

	class viewrenderer_phptal {
		private static $renderer = null;
		private static $extension;
		private static $templatePath;
		private static $compiledPath;

		public static function extension_info() {
			return array(
				'name' => 'viewrenderer: phptal',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'fwversion' => '1.0',
				'enabled' => true,
				'autoevents' => false,
				'depends' => array()
			);
		}

		public static function extension_load() {
			Events::register('renderview', Events::Callback('viewrenderer_phptal::renderview'));

			self::$extension = Config::get('/phptal/templates/@extension', 'zpt');
			self::$templatePath = QPATH_APP . Config::get('/phptal/templates/@templatePath', 'views');
			self::$compiledPath = QPATH_APP . Config::get('/phptal/templates/@compiledPath', 'views/compiled');
		}

		public static function renderview($uObject) {
			if($uObject['viewExtension'] != self::$extension) {
				return;
			}

			if(is_null(self::$renderer)) {
				$tPath = Config::get('/phptal/installation/@path', 'include/3rdparty/PHPTAL');
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

			self::$renderer->setForceReparse(false);
			self::$renderer->setTemplateRepository(self::$templatePath . '/');
			self::$renderer->setPhpCodeDestination(self::$compiledPath . '/');
			self::$renderer->setOutputMode(PHPTAL::HTML5);
			self::$renderer->setEncoding('UTF-8');
			self::$renderer->setTemplate($uObject['viewFile'] . '.' . $uObject['viewExtension']);
			self::$renderer->echoExecute();
		}
	}

?>