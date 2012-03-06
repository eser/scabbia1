<?php

	class usersModel extends Model {
		public function __construct($uController) {
			parent::__construct($uController);

			$this->db->setDatabaseName('dbconn');
		}

		function get($uOffset, $uLimit) {
			$tUsers = database::get('dbconn', 'getUsers')->querySet($uOffset, $uLimit);

			return $tUsers;
		}

		function count() {
			$tCount = database::get('dbconn', 'getUserCount')->queryScalar();
			return (int)$tCount;
		}

		function unsubscribe($uEmail) {
			return database::get('dbconn', 'setUserUnsubscribed')->query($uEmail);
		}
	}

?>