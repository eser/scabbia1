<?php

	/**
	 * RazorViewRendererException represents a generic exception for razor view render extension.
	 *
	 * @author Stepan Kravchenko <stepan.krab@gmail.com>
	 * @version 1.0.0
	 */
	class RazorViewRendererException extends \Exception {
		public function __construct($message, $templateFileName, $line) {
			parent::__construct("Invalid view template: {$templateFileName}, at line {$line}. {$message}", null, null);
		}
	}

?>