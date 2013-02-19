<?php

	namespace Scabbia\Extensions\Router;

	use Scabbia\extensions;
	use Scabbia\Extensions\Profiler\profiler;

	/**
	 * Router Extension
	 *
	 * @package Scabbia
	 * @subpackage router
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.3.0
	 * @scabbia-phpdepends
	 */
	class router {
		/**
		 * @ignore
		 */
		public static function route($uCallbacks, $uOtherwise = null) {
			if(extensions::isLoaded('profiler')) {
				profiler::start('router', array('action' => 'routing'));
			}

			foreach((array)$uCallbacks as $tCallback) {
				$tReturn = call_user_func($tCallback);

				if(!is_null($tReturn) && $tReturn === true) {
					break;
				}
			}

			if(!is_null($uOtherwise) && !isset($tReturn) || $tReturn !== true) {
				call_user_func($uOtherwise);
			}

			if(extensions::isLoaded('profiler')) {
				profiler::stop();
			}
		}
	}

	?>