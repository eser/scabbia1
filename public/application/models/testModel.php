<?php

	class testModel extends Model {
		function insert() {
			return $this->db	->setTable('users')
								->addField('name', 'test3')
								->insert();
		}
		
		function update() {
			return $this->db	->setTable('users')
								->setFields(array('isim' => 'eser', 'soyisim' => 'ozvataf'))
								->addField('yas', '27')
								->setWhere('id=1')
								->andWhere('level<3')
								->setLimit(1)
								->update();
		}

		function delete() {
			return $this->db	->setTable('users')
								->setWhere('name=:name')
								->addParameter(':name', 'test3')
								->setLimit(1)
								->delete();
		}

		function get($uLimit, $uOffset) {
			return $this->db	->setTable('users')
								->setLimit($uLimit)
								->setOffset($uOffset)
								->get();
		}

		function count() {
			return $this->db->calculate('users', 'COUNT');
		}

		function getRow() {
			return $this->db	->setTable('users')
								->setWhere('name=\'test\'')
								->getRow();
		}

		function getScalar() {
			return $this->db	->setTable('users')
								->setFieldsDirect(array('name'))
								->setWhere('name=\'test\'')
								->getScalar();
		}
		
		function getDataset($uLimit, $uOffset) {
			$tUsers = $this->db->datasetSet('getUsers', $uOffset, $uLimit);

			return $tUsers;
		}

		function checkLogin($uName, $uPassword) {
			$tPassword = database::get('dbconn', 'getLoginPassword')->queryScalar($uName);

			if(!is_null($tPassword) && $tPassword == md5($uPassword)) {
				return true;
			}

			return false;
		}

		function getSet($uOffset, $uLimit) {
			$tUsers = database::get('dbconn', 'getUsers')->querySet($uOffset, $uLimit);

			return $tUsers;
		}

		function getSingle($uUserId) {
			return database::get('dbconn', 'getSingleUser')->queryRow($uUserId);
		}

		function countAll() {
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