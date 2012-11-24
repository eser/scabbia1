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
		public static $vars = array();

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

		/**
		 * @ignore
		 */
		public static function get($uKey) {
			return self::$vars[$uKey];
		}

		/**
		 * @ignore
		 */
		public static function set($uKey, $uValue) {
			self::$vars[$uKey] = $uValue;
		}

		/**
		 * @ignore
		 */
		public static function setRef($uKey, &$uValue) {
			self::$vars[$uKey] = & $uValue;
		}

		/**
		 * @ignore
		 */
		public static function remove($uKey) {
			unset(self::$vars[$uKey]);
		}

		/**
		 * @ignore
		 */
		public static function view($uView, $uModel = null) {
			if(is_null($uModel)) {
				$uModel = &self::$vars;
			}

			$tViewFilePath = framework::$applicationPath . 'views/' . $uView;
			$tViewExtension = pathinfo($tViewFilePath, PATHINFO_EXTENSION);
			if(!isset(self::$viewEngines[$tViewExtension])) {
				$tViewExtension = config::get('/mvc/view/defaultViewExtension', 'php');
			}

			$tExtra = array(
				'root' => framework::$siteroot
			);

			if(extensions::isLoaded('i8n')) {
				$tExtra['lang'] = i8n::$language['key'];
			}

			$tTemplatePath = pathinfo($tViewFilePath, PATHINFO_DIRNAME) . '/';
			$tViewFile = pathinfo($tViewFilePath, PATHINFO_BASENAME);

			$tViewArray = array(
				'templatePath' => &$tTemplatePath,
				'templateFile' => &$tViewFile,
				'compiledPath' => framework::writablePath('cache/' . $tViewExtension . '/'),
				'compiledFile' => crc32($tViewFilePath),
				'model' => &$uModel,
				'extra' => &$tExtra
			);

			call_user_func(
				views::$viewEngines[$tViewExtension] . '::renderview',
				$tViewArray
			);
		}

		/**
		 * @ignore
		 */
		public static function viewFile($uView, $uModel = null) {
			if(is_null($uModel)) {
				$uModel = &self::$vars;
			}

			$tViewFilePath = framework::translatePath($uView);
			$tViewExtension = pathinfo($tViewFilePath, PATHINFO_EXTENSION);
			if(!isset(views::$viewEngines[$tViewExtension])) {
				$tViewExtension = config::get('/mvc/view/defaultViewExtension', 'php');
			}

			$tExtra = array(
				'root' => framework::$siteroot
			);

			if(extensions::isLoaded('i8n')) {
				$tExtra['lang'] = i8n::$language['key'];
			}

			$tTemplatePath = pathinfo($tViewFilePath, PATHINFO_DIRNAME) . '/';
			$tViewFile = pathinfo($tViewFilePath, PATHINFO_BASENAME);

			$tViewArray = array(
				'templatePath' => &$tTemplatePath,
				'templateFile' => &$tViewFile,
				'compiledPath' => framework::writablePath('cache/' . $tViewExtension . '/'),
				'compiledFile' => crc32($uView),
				'model' => &$uModel,
				'extra' => &$tExtra
			);

			call_user_func(
				views::$viewEngines[$tViewExtension] . '::renderview',
				$tViewArray
			);
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