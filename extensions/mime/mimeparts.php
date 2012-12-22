<?php

	/**
	 * Mime Extension: Mimeparts
	 *
	 * @package Scabbia
	 * @subpackage mime
	 * @version 1.0.2
	 *
	 * @scabbia-fwversion 1.0
	 * @scabbia-fwdepends string
	 * @scabbia-phpversion 5.2.0
	 * @scabbia-phpdepends
	 */
	class multipart {
		const NONE = 0;
		const RELATED = 1;

		public $headers = array();
		public $linesAfterHeaders = 1;
		public $boundaryName;
		public $parts = array();
		public $filename;

		/**
		 * @ignore
		 */
		public function &compileBody() {
			$tString = '';

			foreach($this->parts as &$tPart) {
				$tString .= '--' . $this->boundaryName . "\n" . $tPart->compile(true, true) . "\n";
			}

			$tString .= '--' . $this->boundaryName . '--';

			return $tString;
		}

		/**
		 * @ignore
		 */
		public function &compile($uHeaders = true, $uContent = true) {
			$tString = '';
			$tBody = $this->compileBody();

			if($uHeaders) {
				$tHeaders = $this->headers;

				if(count($this->parts) > 0) {
					$tPart = $this->parts[0];

					if(!array_key_exists('Content-Type', $tHeaders)) {
						$tHeaders['Content-Type'] = 'multipart/related; boundary=' . $this->boundaryName;
						if(array_key_exists('Content-Id', $tPart->headers)) {
							$tHeaders['Content-Type'] .= '; start="' . $tPart->headers['Content-Id'] . '"';
						}

						if(array_key_exists('Content-Type', $tPart->headers)) {
							$tContentType = explode(';', $tPart->headers['Content-Type'], 2);
							$tHeaders['Content-Type'] .= '; type="' . $tContentType[0] . '"';
						}
					}
				}

				if(!array_key_exists('Content-Disposition', $tHeaders) && strlen($this->filename) > 0) {
					$tHeaders['Content-Disposition'] = 'attachment; filename=' . $this->filename;
				}

				if(!array_key_exists('Content-Length', $tHeaders)) {
					$tHeaders['Content-Length'] = strlen($tBody);
				}

				foreach($tHeaders as $tKey => &$tValue) {
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
		public function &addPart() {
			$tNewPart = new mimepart();
			$this->parts[] = $tNewPart;

			return $tNewPart;
		}
	}

	class mimepart {
		public $headers = array();
		public $linesAfterHeaders = 1;
		public $transferEncoding = 'base64';
		public $filename;
		public $content;

		/**
		 * @ignore
		 */
		public function &compileBody() {
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
		public function &compile($uHeaders = true, $uContent = true) {
			$tString = '';
			$tBody = $this->compileBody();

			if($uHeaders) {
				$tHeaders = $this->headers;
				if(!array_key_exists('Content-Id', $tHeaders)) {
					$tHeaders['Content-Id'] = '<' . string::generate(15) . '>';
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

				foreach($tHeaders as $tKey => &$tValue) {
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