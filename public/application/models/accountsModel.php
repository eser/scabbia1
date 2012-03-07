<?php

	class accountsModel extends Model {
//		public function __construct($uController) {
//			parent::__construct($uController);
//
//			$this->db->setDatabaseName('dbconn');
//		}

		function checkLogin($uName, $uPassword) {
			$tPassword = database::get('dbconn', 'getLoginPassword')->queryScalar($uName);

			if(!is_null($tPassword) && $tPassword == md5($uPassword)) {
				return true;
			}

			return false;
		}
	}

?>