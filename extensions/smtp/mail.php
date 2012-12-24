<?php

	/**
	 * SMTP Mail Class
	 *
	 * @package Scabbia
	 * @subpackage LayerExtensions
	 */
	class mail {
		/**
		 * @ignore
		 */
		public $from;
		/**
		 * @ignore
		 */
		public $to;
		/**
		 * @ignore
		 */
		public $subject;
		/**
		 * @ignore
		 */
		public $content;
		/**
		 * @ignore
		 */
		public $parts = array();

		/**
		 * @ignore
		 */
		public function &addPart($uFilename, $uContent, $uEncoding = '8bit', $uType = null) {
			$tMimepart = new mimepart();
			$tMimepart->filename = $uFilename;

			if(!is_null($uType)) {
				$tMimepart->type = $uType;
			}
			else {
				$tExtension = pathinfo($uFilename, PATHINFO_EXTENSION);
				$tMimepart->type = mime::getType($tExtension, 'text/plain');
			}

			
			$tMimepart->transferEncoding = $uEncoding;
			$tMimepart->content = $uContent;

			$this->parts[] = &$tMimepart;
			return $tMimepart;
		}

		/**
		 * @ignore
		 */
		public function &addAttachment($uFilename, $uPath, $uEncoding = 'base64', $uType = null) {
			$tMimepart = new mimepart();
			$tMimepart->filename = $uFilename;

			if(!is_null($uType)) {
				$tMimepart->type = $uType;
			}
			else {
				$tExtension = pathinfo($uFilename, PATHINFO_EXTENSION);
				$tMimepart->type = mime::getType($tExtension, 'application/octet-stream');
			}

			$tMimepart->transferEncoding = $uEncoding;
			$tMimepart->load($uPath);

			$this->parts[] = &$tMimepart;
			return $tMimepart;
		}

		/**
		 * @ignore
		 */
		public function getContent() {
			if(count($this->parts) > 0) {
				$tMain = new multipart();
				$tMain->boundaryName = 'mail';
				$tMain->filename = 'mail.eml';

				if(!array_key_exists('From', $tMain->headers)) {
					$tMain->headers['From'] = $this->from;
				}
				if(!array_key_exists('To', $tMain->headers)) {
					$tMain->headers['To'] = $this->to;
				}
				if(!array_key_exists('Subject', $tMain->headers)) {
					$tMain->headers['Subject'] = $this->subject;
				}

				foreach($this->parts as &$tPart) {
					$tMain->parts[] = $tPart;
				}

				return $tMain->compile();
			}

			return $this->content;
		}

		/**
		 * @ignore
		 */
		public function send() {
			smtp::send($this->from, $this->to, $this->getContent());
		}
	}

?>