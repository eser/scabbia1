<?php

	namespace Scabbia\Extensions\Views;

	/**
	 * ViewEngine: PHP
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class viewEnginePhp {
		/**
		 * @ignore
		 */
		public static function renderview($uObject) {
			// variable extraction
			$model = $uObject['model'];
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