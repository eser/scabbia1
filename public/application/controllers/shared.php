<?php

	class shared extends Controller {
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