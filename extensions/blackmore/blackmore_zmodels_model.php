<?php	class blackmoreZmodelModel extends model {		public function insert($uTable, $uInput) {			$tTime = time::toDb(time());			return $this->db->createQuery()								->setTable($uTable)								->setFields($uInput)								->addField('categoryid', string::generateUuid())								->addField('createdate', $tTime)								->addField('updatedate', $tTime)								->setReturning('categoryid')								->insert()								->execute();		}		public function update($uTable, $uCategoryId, $uInput) {			$tTime = time::toDb(time());			return $this->db->createQuery()								->setTable($uTable)								->setFields($uInput)								->addField('updatedate', $tTime)								->addParameter('categoryid', $uCategoryId)								->setWhere('categoryid=:categoryid')								->andWhere('deletedate IS NULL')								->setLimit(1)								->update()								->execute();		}		public function deletePhysically($uTable, $uCategoryId) {			return $this->db->createQuery()								->setTable($uTable)								->addParameter('categoryid', $uCategoryId)								->setWhere('categoryid=:categoryid')								->setLimit(1)								->delete()								->execute();		}		public function delete($uTable, $uCategoryId) {			$tTime = time::toDb(time());			return $this->db->createQuery()								->setTable($uTable)								->addField('deletedate', $tTime)								->addParameter('categoryid', $uCategoryId)								->setWhere('categoryid=:categoryid')								->andWhere('deletedate IS NULL')								->setLimit(1)								->update()								->execute();		}		public function deleteBySlug($uTable, $uSlug) {			$tTime = time::toDb(time());			return $this->db->createQuery()								->setTable($uTable)								->addField('deletedate', $tTime)								->addParameter('slug', $uSlug)								->setWhere('slug=:slug')								->andWhere('deletedate IS NULL')								->setLimit(1)								->update()								->execute();		}		public function get($uTable, $uCategoryId) {			return $this->db->createQuery()								->setTable($uTable)								->addField('*')								->addParameter('categoryid', $uCategoryId)								->setWhere('categoryid=:categoryid')								->andWhere('deletedate IS NULL')								->setLimit(1)								->get()								->row();		}		public function getBySlug($uTable, $uSlug) {			return $this->db->createQuery()								->setTable($uTable)								->addField('*')								->addParameter('slug', $uSlug)								->setWhere('slug=:slug')								->andWhere('deletedate IS NULL')								->setLimit(1)								->get()								->row();		}		public function getAll($uTable) {			return $this->db->createQuery()								->setTable($uTable)								->addField('*')								->setWhere('deletedate IS NULL')								->setOrderBy('createdate', 'DESC')								->get()								->all();		}		public function getAllByType($uTable, $uType) {			return $this->db->createQuery()								->setTable($uTable)								->addField('*')								->addParameter('type', $uType)								->setWhere('deletedate IS NULL')								->andWhere('type=:type')								->setOrderBy('createdate', 'DESC')								->get()								->all();		}		public function getAllAsPairs($uTable) {			$tQuery = $this->db->createQuery()								->setTable($uTable)								->addField('*')								->setWhere('deletedate IS NULL')								->setOrderBy('createdate', 'DESC')								->get();			$tArray = array();			foreach($tQuery as $tRow) {				$tArray[$tRow['categoryid']] = $tRow;			}			$tQuery->close();			return $tArray;		}		public function getAsPairs($uTable, $uType) {			$tQuery = $this->db->createQuery()								->setTable($uTable)								->addField('*')								->addParameter('type', $uType)								->setWhere('deletedate IS NULL')								->andWhere('type=:type')								->setOrderBy('createdate', 'DESC')								->get();			$tArray = array();			foreach($tQuery as $tRow) {				$tArray[$tRow['categoryid']] = $tRow;			}			$tQuery->close();			return $tArray;		}		public function count($uTable) {			return $this->db->calculate($uTable, 'COUNT', '*', 'deletedate IS NULL');		}	}?>