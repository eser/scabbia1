<?php

	namespace Scabbia;

	/**
	 * Mimepart Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class mimepart {
		/**
		 * @ignore
		 */
		public $headers = array();
		/**
		 * @ignore
		 */
		public $linesAfterHeaders = 1;
		/**
		 * @ignore
		 */
		public $type = 'text/plain';
		/**
		 * @ignore
		 */
		public $transferEncoding = 'base64';
		/**
		 * @ignore
		 */
		public $filename;
		/**
		 * @ignore
		 */
		public $content;

		/**
		 * @ignore
		 */
		public function compileBody() {
			$tString = '';

			if($this->transferEncoding == 'base64') {
				$tString .= chunk_split(base64_encode($this->content));
			}
			else {
				$tString .= $this->content;
			}

			return $tString;
		}

		/**
		 * @ignore
		 */
		public function compile($uHeaders = true) {
			$tString = '';
			$tBody = $this->compileBody();

			if($uHeaders) {
				$tHeaders = &$this->headers;
				if(!array_key_exists('Content-Id', $tHeaders)) {
					$tHeaders['Content-Id'] = '<' . string::generate(15) . '>';
				}

				if(!array_key_exists('Content-Type', $tHeaders)) {
					$tHeaders['Content-Type'] = $this->type;
				}

				if(!array_key_exists('Content-Transfer-Encoding', $tHeaders)) {
					$tHeaders['Content-Transfer-Encoding'] = $this->transferEncoding;
				}

				if(!array_key_exists('Content-Disposition', $tHeaders) && strlen($this->filename) > 0) {
					$tHeaders['Content-Disposition'] = 'attachment; filename=' . $this->filename;
				}

				if(!array_key_exists('Content-Length', $tHeaders)) {
					$tHeaders['Content-Length'] = strlen($tBody);
				}

				foreach($tHeaders as $tKey => $tValue) {
					$tString .= $tKey . ': ' . $tValue . "\n";
				}

				for($i = $this->linesAfterHeaders; $i > 0; $i--) {
					$tString .= "\n";
				}
			}

			$tString .= $tBody;

			return $tString;
		}

		/**
		 * @ignore
		 */
		public function load($uFilename) {
			$this->content = file_get_contents($uFilename);
		}
	}

?>