<?php

	class home extends Controller {
		public function index() {
			$this->set('message', 'testing...');
			$this->view();
		}
	}

?>