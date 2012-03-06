<?php

	class users extends Controller {
		public function unsubscribe() {
			$this->loadmodel('usersModel', 'users');

			$tEmail = $this->httpGet(2, '');
			if(empty($tEmail)) {
				return $this->error('user is empty');
			}

			$tResult = $this->users->unsubscribe($tEmail);

			$tViewbag = array(
				'title' => 'Done'
			);

			if($tResult) {
				$tViewbag['message'] = 'Your e-mail is unsubscribed from mailing list. You won\'t get any notification mails from now on.';
			}
			else {
				$tViewbag['message'] = 'Your e-mail has already been unsubscribed from mailing list.';
			}

			$this->loadview('shared_error.cshtml', $tViewbag);
		}
		
		public function error($uMsg) {
			$tViewbag = array(
				'title' => 'Error',
				'message' => $uMsg
			);

			$this->loadview('shared_error.cshtml', $tViewbag);
		}
		
		public function notfound() {
			return $this->error('404 not found!');
		}
	}

?>