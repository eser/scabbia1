<?php

	class users extends Controller {
		public function unsubscribe() {
			$tEmail = $this->httpGet(2, '');
			if(empty($tEmail)) {
				return $this->error('user is empty');
			}

			$this->loadmodel('usersModel', 'users');
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

		public function image() {
			$tCampaign = $this->httpGet(2, '');
			if(empty($tCampaign)) {
				return $this->error('campaign is empty');
			}

			$tUserId = $this->httpGet(3, '');
			if(empty($tUserId)) {
				return $this->error('user is empty');
			}

			$this->loadmodel('usersModel', 'users');
			$this->users->logCampaignView($tUserId, $tCampaign, 2); // 2=image

			http::sendFile(QPATH_CORE . 'res/eposta.png');
		}

		public function content() {
			$tCampaign = $this->httpGet(2, '');
			if(empty($tCampaign)) {
				return $this->error('campaign is empty');
			}

			$tUserId = $this->httpGet(3, '');
			if(empty($tUserId)) {
				return $this->error('user is empty');
			}

			$this->loadmodel('usersModel', 'users');
			
			$tUser = $this->users->getSingle($tUserId);
			if(is_null($tUser)) {
				return $this->error('user is not exists');
			}

			$this->users->logCampaignView($tUserId, $tCampaign, 1); // 1=content

			$tViewbag = array(
				'title' => $tUser['LongName'],
				'longname' => $tUser['LongName'],
				'email' => $tUser['EMail'],
				'facebookid' => $tUser['facebookid'],
				'imgpath' => $tUser['ImgPath'],
				'gender' => $tUser['Gender'],
				'locale' => $tUser['Locale'],
				'recdate' => $tUser['RecDate'],
				'campaign' => $tCampaign,
				'userid' => $tUserId,
				'image' => $_SERVER['PHP_SELF'] . '?users/image/' . $tCampaign . '/' . $tUserId
			);

			$this->loadview('users_content.cshtml', $tViewbag);
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