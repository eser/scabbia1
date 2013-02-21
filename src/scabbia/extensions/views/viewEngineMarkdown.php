<?php

	namespace Scabbia\Extensions\Views;

	use Scabbia\Extensions\Cache\cache;
	use Scabbia\Extensions\Io\io;
	use Scabbia\Extensions\Views\views;
	use Scabbia\config;
	use Scabbia\framework;

	include 'markdownExtra/markdownExtra.php';
	include 'markdownExtra/markdownParser.php';
	include 'markdownExtra/markdownExtraParser.php';

	/**
	 * ViewEngine: MarkDown Extension
	 *
	 * @package Scabbia
	 * @subpackage viewEngineMarkdown
	 * @version 1.1.0
	 *
	 * @scabbia-fwversion 1.1
	 * @scabbia-fwdepends mvc, io, cache
	 * @scabbia-phpversion 5.3.0
	 * @scabbia-phpdepends
	 */
	class viewEngineMarkdown {
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
			views::registerViewEngine('md', 'viewEngineMarkdown');
		}

		/**
		 * @ignore
		 */
		public static function renderview($uObject) {
			$tInputFile = $uObject['templatePath'] . $uObject['templateFile'];

			$tOutputFile = cache::filePath('md/', $uObject['compiledFile'], self::$compiledAge);
			if(framework::$development >= 1 || !$tOutputFile[0]) {
				if(is_null(self::$engine)) {
					self::$engine = new \MarkdownExtra_Parser();
				}

				$tInput = io::read($tInputFile);
				$tOutput = self::$engine->transform($tInput);

				if(!is_null($tOutputFile[1])) {
					io::write($tOutputFile[1], $tOutput);
				}
				echo $tOutput;
			}
			else {
				require($tOutputFile[1]);
			}
		}
	}

	?>