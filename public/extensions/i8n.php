<?php

if(Extensions::isSelected('i8n')) {
	class i8n {
		public static $languages = array();
		public static $language = null;

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

		public static function extension_load() {
			foreach(Config::get('/i8n/languageList', array()) as $tLanguage) {
				self::$languages[$tLanguage['@id']] = array(
					'key' => $tLanguage['@id'],
					'name' => $tLanguage['.']
				);
			}

			$tLanguageKey = Config::get('/i8n/routing/@languageUrlKey', null);

			if(!is_null($tLanguageKey)) {
				if(array_key_exists($tLanguageKey, $_GET)) {
					if(self::setLanguage($_GET[$tLanguageKey], true)) {
						return;
					}
				}
			}

			if(!PHP_SAPI_CLI) {
				foreach(http::$languages as $tLanguage) {
					if(self::setLanguage($tLanguage, false)) {
						return;
					}
				}
			}

			foreach(array_keys(self::$languages) as $tLanguage) {
				if(self::setLanguage($tLanguage, false)) {
					return;
				}
			}
		}

		private static function setLanguage($uLanguage, $uLastChoice = false) {
			if(array_key_exists($uLanguage, self::$languages)) {
				self::$language = self::$languages[$uLanguage];
				return true;
			}

			if($uLastChoice) {
				$tExploded = explode('-', $uLanguage, 2);

				if(array_key_exists($tExploded[0], self::$languages)) {
					self::$language = self::$languages[$tExploded[0]];
					return true;
				}
			}

			return false;
		}
	}
}

?>
