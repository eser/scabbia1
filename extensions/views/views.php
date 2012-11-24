<?php

	/**
	 * Views Extension
	 *
	 * @package Scabbia
	 * @subpackage views
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string, http
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class views {
		/**
		 * @ignore
		 */
		public static $viewEngines = array();

		/**
		 * @ignore
		 */
		public static function extension_load() {
			foreach(config::get('/mvc/view/viewEngineList', array()) as $tViewEngine) {
				self::registerViewEngine($tViewEngine['extension'], $tViewEngine['class']);
			}

			self::registerViewEngine('php', 'viewengine_php');
		}

		/**
		 * @ignore
		 */
		public static function registerViewEngine($uExtension, $uClassName) {
			if(isset(self::$viewEngines[$uExtension])) {
				return;
			}

			self::$viewEngines[$uExtension] = $uClassName;
		}
	}

	/**
	 * ViewEngine: PHP
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class viewengine_php {
		/**
		 * @ignore
		 */
		public static function renderview($uObject) {
			// variable extraction
			$model = & $uObject['model'];
			if(is_array($model)) {
				extract($model, EXTR_SKIP | EXTR_REFS);
			}

			if(isset($uObject['extra'])) {
				extract($uObject['extra'], EXTR_SKIP | EXTR_REFS);
			}

			require($uObject['templatePath'] . $uObject['templateFile']);
		}
	}

?>