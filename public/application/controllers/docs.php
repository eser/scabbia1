<?php

	class docs extends Controller {
		public function index() {
			$this->loadview(null, 'docs_index.md');
		}
	}

?>