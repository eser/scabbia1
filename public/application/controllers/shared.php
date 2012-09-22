<?php

	class shared extends controller {
		public function notfound() {
			$this->view();
		}

		public function error() {
			$this->view();
		}

		public function ipban() {
			$this->view();
		}

		public function maintenance() {
			$this->view();
		}
	}

?>