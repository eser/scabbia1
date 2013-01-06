<?php

	namespace Scabbia;

	/**
	 * I8N Extension
	 *
	 * @package Scabbia
	 * @subpackage i8n
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends mbstring
	 *
	 * @todo translitIt
	 * @todo intl extension methods
	 */
	class i8n {
		/**
		 * @ignore
		 */
		public static $languages = null;
		/**
		 * @ignore
		 */
		public static $language = null;

		/**
		 * @ignore
		 */
		/*
		public static function extensionLoad() {
			// Use the Universal Coordinated Time and most common English standards
			date_default_timezone_set('UTC');

			//! todo: determine language by browser's language priorities.
		}
		*/

		/**
		 * @ignore
		 */
		public static function httpUrl(&$uParms) {
			$uParms['language'] = self::$language['key'];
		}

		/**
		 * @ignore
		 */
		public static function setLanguage($uLanguage, $uLastChoice = false) {
			if(is_null(self::$languages)) {
				self::$languages = array();

				foreach(config::get('/i8n/languageList', array()) as $tLanguage) {
					self::$languages[$tLanguage['id']] = array(
						'key' => $tLanguage['id'],
						'locale' => $tLanguage['locale'],
						// 'localewin' => $tLanguage['@localewin'],
						'internalEncoding' => $tLanguage['internalEncoding'],
						'name' => $tLanguage['name']
					);
				}
			}

			if(array_key_exists($uLanguage, self::$languages)) {
				self::$language = self::$languages[$uLanguage];
			}
			else {
				if($uLastChoice) {
					$tExploded = explode('-', $uLanguage, 2);

					if(array_key_exists($tExploded[0], self::$languages)) {
						self::$language = self::$languages[$tExploded[0]];
					}
				}
			}

			if(!is_null(self::$language)) {
				// if(DIRECTORY_SEPARATOR == '\\') {
				// 	$tLocale = explode('.', self::$language['localewin'], 2);
				// }
				// else {
				$tLocale = explode('.', self::$language['locale'], 2);
				// }

				$tLocale['all'] = implode('.', $tLocale);

				// putenv('LC_ALL=' . $tLocale[0]);
				if(!PHP_SAFEMODE) {
					putenv('LANG=' . $tLocale[0]);
				}
				setlocale(LC_ALL, $tLocale[0]);

				mb_internal_encoding(self::$language['internalEncoding']);
				mb_http_output(self::$language['internalEncoding']);

				// bindtextdomain('core', QPATH_CORE . 'locale');
				// bind_textdomain_codeset('core', self::$language['internalEncoding']);

				bindtextdomain('application', framework::$applicationPath . 'locale');
				bind_textdomain_codeset('application', self::$language['internalEncoding']);

				textdomain('application');

				return true;
			}

			return false;
		}
	}

?>