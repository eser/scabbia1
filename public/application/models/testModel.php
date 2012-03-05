<?php

	class testModel extends Model {
		public function __construct($uController) {
			parent::__construct($uController);

			$this->db->setDatabaseName('dbconn');
		}

		function insert() {
			return $this->db	->setTable('testtable')
								->addField('name', 'test3')
								->insert();
		}
		
		function update() {
			return $this->db	->setTable('testtable')
								->setFields(array('isim' => 'eser', 'soyisim' => 'ozvataf'))
								->addField('yas', '27')
								->setWhere('id=1')
								->andWhere('level<3')
								->setLimit(1)
								->update();
		}

		function delete() {
			return $this->db	->setTable('testtable')
								->setWhere('name=:name')
								->addParameter(':name', 'test3')
								->setLimit(1)
								->delete();
		}

		function get($uLimit, $uOffset) {
			return $this->db	->setTable('testtable')
								->setLimit($uLimit)
								->setOffset($uOffset)
								->get();
		}

		function count() {
			return $this->db->calculate('testtable', 'COUNT');
		}

		function getRow() {
			return $this->db	->setTable('testtable')
								->setWhere('name=\'test\'')
								->getRow();
		}

		function getScalar() {
			return $this->db	->setTable('testtable')
								->setFieldsDirect(array('name'))
								->setWhere('name=\'test\'')
								->getScalar();
		}
	}

?>