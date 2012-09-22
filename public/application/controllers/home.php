<?php

	class home extends controller {
		public function index() {
			$this->set('message', 'testing...');
			$this->view();
		}
	}

?>