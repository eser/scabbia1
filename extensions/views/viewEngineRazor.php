<?php

	/**
	 * ViewEngine: Razor Extension
	 *
	 * @package Scabbia
	 * @subpackage viewEngineRazor
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends mvc, cache
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class viewEngineRazor {
		/**
		 * @ignore
		 */
		public static $engine = null;
		/**
		 * @ignore
		 */
		public static $compiledAge;

		/**
		 * @ignore
		 */
		public static function extensionLoad() {
			self::$compiledAge = intval(config::get('/razor/templates/compiledAge', '120'));
			views::registerViewEngine('cshtml', 'viewEngineRazor');
		}

		/**
		 * @ignore
		 */
		public static function renderview($uObject) {
			$tInputFile = $uObject['templatePath'] . $uObject['templateFile'];

			// cengiz: Render if file not exist
			// or debug mode on
			$tOutputFile = cache::filePath('cshtml/', $uObject['compiledFile'], self::$compiledAge);
			if(framework::$development >= 1 || !$tOutputFile[0]) {
				if(is_null(self::$engine)) {
					self::$engine = new RazorViewRenderer();
				}

				self::$engine->generateViewFile($tInputFile, $tOutputFile[1]);
			}

			// variable extraction
			$model = $uObject['model'];
			if(is_array($model)) {
				extract($model, EXTR_SKIP | EXTR_REFS);
			}

			if(isset($uObject['extra'])) {
				extract($uObject['extra'], EXTR_SKIP | EXTR_REFS);
			}

			require($tOutputFile[1]);
		}
	}

?>