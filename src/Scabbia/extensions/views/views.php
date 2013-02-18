<?php

	namespace Scabbia\Extensions\Views;

	/**
	 * Views Extension
	 *
	 * @package Scabbia
	 * @subpackage views
	 * @version 1.0.5
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string, http, resources, cache
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
		public static function extensionLoad() {
			foreach(config::get('/mvc/view/viewEngineList', array()) as $tViewEngine) {
				self::registerViewEngine($tViewEngine['extension'], $tViewEngine['class']);
			}

			self::registerViewEngine('php', 'viewEnginePhp');
		}

		/**
		 * @ignore
		 */
		public static function registerViewEngine($uExtension, $uClassName) {
			if(isset(self::$viewEngines[$uExtension])) {
				return;
			}

			self::$viewEngines[$uExtension] = 'Scabbia\\Extensions\\Views\\' . $uClassName;
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
			self::$vars[$uKey] = $uValue;
		}

		/**
		 * @ignore
		 */
		public static function setRange($uArray) {
			foreach($uArray as $tKey => $tValue) {
				self::$vars[$tKey] = $tValue;
			}
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
				$uModel = & self::$vars;
			}

			$tViewFilePath = framework::$applicationPath . 'views/' . $uView;
			$tViewFileInfo = pathinfo($tViewFilePath);
			if(!isset(self::$viewEngines[$tViewFileInfo['extension']])) {
				$tViewFileInfo['extension'] = config::get('/mvc/view/defaultViewExtension', 'php');
			}

			$tExtra = array(
				'root' => rtrim(framework::$siteroot, '/')
			);

			if(extensions::isLoaded('i8n')) {
				$tExtra['lang'] = i8n::$language['key'];
			}

			if(extensions::isLoaded('mvc')) {
				$tExtra['controller'] = mvc::current();
			}

			$tTemplatePath = pathinfo($tViewFilePath, PATHINFO_DIRNAME) . '/';
			$tViewFile = pathinfo($tViewFilePath, PATHINFO_BASENAME);

			$tViewArray = array(
				'templatePath' => &$tTemplatePath,
				'templateFile' => &$tViewFile,
				'compiledFile' => hash('adler32', $tViewFilePath) . '-' . $tViewFileInfo['basename'],
				'model' => &$uModel,
				'extra' => &$tExtra
			);

			call_user_func(
				views::$viewEngines[$tViewFileInfo['extension']] . '::renderview',
				$tViewArray
			);
		}

		/**
		 * @ignore
		 */
		public static function viewFile($uView, $uModel = null) {
			if(is_null($uModel)) {
				$uModel = & self::$vars;
			}

			$tViewFilePath = framework::translatePath($uView);
			$tViewFileInfo = pathinfo($tViewFilePath);
			if(!isset(views::$viewEngines[$tViewFileInfo['extension']])) {
				$tViewFileInfo['extension'] = config::get('/mvc/view/defaultViewExtension', 'php');
			}

			$tExtra = array(
				'root' => framework::$siteroot
			);

			if(extensions::isLoaded('i8n')) {
				$tExtra['lang'] = i8n::$language['key'];
			}

			if(extensions::isLoaded('mvc')) {
				$tExtra['controller'] = mvc::current();
			}

			$tTemplatePath = pathinfo($tViewFilePath, PATHINFO_DIRNAME) . '/';
			$tViewFile = pathinfo($tViewFilePath, PATHINFO_BASENAME);

			$tViewArray = array(
				'templatePath' => &$tTemplatePath,
				'templateFile' => &$tViewFile,
				'compiledFile' => hash('adler32', $uView) . '-' . $tViewFileInfo['basename'],
				'model' => &$uModel,
				'extra' => &$tExtra
			);

			call_user_func(
				views::$viewEngines[$tViewFileInfo['extension']] . '::renderview',
				$tViewArray
			);
		}

		/**
		 * @ignore
		 */
		public static function json($uModel = null) {
			if(is_null($uModel)) {
				$uModel = & self::$vars;
			}

			header('Content-Type: application/json', true);

			echo json_encode(
				$uModel
			);
		}

		/**
		 * @ignore
		 */
		public static function xml($uModel = null) {
			if(is_null($uModel)) {
				$uModel = & self::$vars;
			}

			header('Content-Type: application/xml', true);

			echo '<?xml version="1.0" encoding="UTF-8" ?>';
			echo '<xml>';
			self::xml_recursive($uModel);
			echo '</xml>';
		}

		/**
		 * @ignore
		 */
		private static function xml_recursive($uObject) {
			if(is_array($uObject) || is_object($uObject)) {
				foreach($uObject as $tKey => $tValue) {
					if(is_numeric($tKey)) {
						echo '<item index="' . $tKey . '">';
						$tKey = 'item';
					}
					else {
						echo '<' . $tKey . '>';
					}

					echo self::xml_recursive($tValue);
					echo '</' . $tKey . '>';
				}

				return;
			}

			echo htmlspecialchars($uObject, ENT_NOQUOTES);
		}
	}

	?>