<?php

	namespace Scabbia;

	/**
	 * Multipart Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class multipart {
		/**
		 * @ignore
		 */
		const RELATED = 0;
		/**
		 * @ignore
		 */
		const ALTERNATIVE = 1;

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
		public $boundaryName;
		/**
		 * @ignore
		 */
		public $boundaryType;
		/**
		 * @ignore
		 */
		public $content = 'This is a multi-part message in MIME format.';
		/**
		 * @ignore
		 */
		public $parts = array();
		/**
		 * @ignore
		 */
		public $filename;

		/**
		 * @ignore
		 */
		public function multipart($uBoundaryName = 'mimeboundary', $uBoundaryType = self::ALTERNATIVE) {
			$this->boundaryName = $uBoundaryName;
			$this->boundaryType = $uBoundaryType;
		}

		/**
		 * @ignore
		 */
		public function compileBody() {
			$tString = $this->content . "\n\n";

			foreach($this->parts as $tPart) {
				$tString .= '--' . $this->boundaryName . "\n" . $tPart->compile(true) . "\n";
			}

			$tString .= '--' . $this->boundaryName . '--';

			return $tString;
		}

		/**
		 * @ignore
		 */
		public function compile($uHeaders = true) {
			$tString = '';
			$tBody = $this->compileBody();

			if($uHeaders) {
				$tHeaders = & $this->headers;
				if(!array_key_exists('MIME-Version', $tHeaders)) {
					$tHeaders['MIME-Version'] = '1.0';
				}

				if(count($this->parts) > 0) {
					$tPart = $this->parts[0];

					if(!array_key_exists('Content-Type', $tHeaders)) {
						if($this->boundaryType == self::ALTERNATIVE) {
							$tHeaders['Content-Type'] = 'multipart/alternative; boundary=' . $this->boundaryName;
						}
						else {
							$tHeaders['Content-Type'] = 'multipart/related; boundary=' . $this->boundaryName;

							if(array_key_exists('Content-Id', $tPart->headers)) {
								$tHeaders['Content-Type'] .= '; start="' . $tPart->headers['Content-Id'] . '"';
							}
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
		public function addPart() {
			$tNewPart = new mimepart();
			$this->parts[] = $tNewPart;

			return $tNewPart;
		}
	}

	?>