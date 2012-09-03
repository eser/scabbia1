<?php

if(extensions::isSelected('i8n')) {
	/**
	* I8N Extension
	*
	* @package Scabbia
	* @subpackage LayerExtensions
	*
	* @todo translitIt
	*/
	class i8n {
		/**
		* @ignore
		*/
		public static $languages = array();
		/**
		* @ignore
		*/
		public static $language = null;

		/**
		* @ignore
		*/
		public static function extension_info() {
			return array(
				'name' => 'i8n',
				'version' => '1.0.2',
				'phpversion' => '5.1.0',
				'phpdepends' => array(),
				'fwversion' => '1.0',
				'fwdepends' => array('http')
			);
		}

		/**
		* @ignore
		*/
		public static function extension_load() {
			foreach(config::get('/i8n/languageList', array()) as $tLanguage) {
				self::$languages[$tLanguage['@id']] = array(
					'key' => $tLanguage['@id'],
					'locale' => $tLanguage['@locale'],
					'name' => $tLanguage['.']
				);

				if(!isset(self::$language)) {
					self::$language = self::$languages[$tLanguage['@id']];
				}
			}
		}

		/**
		* @ignore
		*/
		public static function setLanguage($uLanguage, $uLastChoice = false) {
			if(array_key_exists($uLanguage, self::$languages)) {
				$tLanguage = &self::$languages[$uLanguage];
			}
			else if($uLastChoice) {
				$tExploded = explode('-', $uLanguage, 2);

				if(array_key_exists($tExploded[0], self::$languages)) {
					$tLanguage = &self::$languages[$tExploded[0]];
				}
			}

			if(isset($tLanguage)) {
				$tLocale = explode('.', $tLanguage['locale'], 2);
				if(!isset($tLocale[1])) {
					$tLocale[1] = 'UTF-8';
				}

				putenv('LC_ALL=' . $tLocale[0]);
				setlocale(LC_ALL, $tLocale[0]);

				mb_internal_encoding($tLocale[1]);
				mb_http_output($tLocale[1]);

				bindtextdomain('core', QPATH_CORE . 'locale');
				bind_textdomain_codeset('core', $tLocale[1]);

				bindtextdomain('application', framework::$applicationPath . 'locale');
				bind_textdomain_codeset('application', $tLocale[1]);

				return true;
			}

			return false;
		}
	}
}

?>