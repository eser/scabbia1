<?php

	class usersModel extends Model {
//		public function __construct($uController) {
//			parent::__construct($uController);
//
//			$this->db->setDatabaseName('dbconn');
//		}

		function get($uOffset, $uLimit) {
			$tUsers = database::get('dbconn', 'getUsers')->querySet($uOffset, $uLimit);

			return $tUsers;
		}

		function getSingle($uUserId) {
			return database::get('dbconn', 'getSingleUser')->queryRow($uUserId);
		}

		function count() {
			$tCount = database::get('dbconn', 'getUserCount')->queryScalar();
			return (int)$tCount;
		}

		function unsubscribe($uEmail) {
			return database::get('dbconn', 'setUserUnsubscribed')->query($uEmail);
		}

		function logCampaignView($uUserId, $uCampaign, $uOperation) {
			try {
				return database::get('dbconn', 'logCampaignView')->query($uUserId, $uCampaign, $uOperation);
			}
			catch(PDOException $ex) {
				return false;
			}
		}
	}

?>