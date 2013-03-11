<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Zmodels;

use Scabbia\Extensions\Zmodels\Zmodels;

/**
 * Zmodels Extension: Zmodel Class
 *
 * @package Scabbia
 * @subpackage Zmodels
 * @version 1.1.0
 */
class zmodel
{
    /**
     * @ignore
     */
    public $entityName;
    /**
     * @ignore
     */
    public $entityDefinition;
    /**
     * @ignore
     */
    public $fields = array();


    /**
     * @ignore
     */
    public function zmodel($uEntityName)
    {
        $this->entityName = $uEntityName;
        $this->entityDefinition = Zmodels::$zmodels[$uEntityName];
    }

    /**
     * @ignore
     */
    public function __isset($uName)
    {
        return isset($this->fields[$uName]);
    }

    /**
     * @ignore
     */
    public function __unset($uName)
    {
        unset($this->fields[$uName]);
    }

    /**
     * @ignore
     */
    public function __get($uName)
    {
        return $this->fields[$uName];
    }

    /**
     * @ignore
     */
    public function __set($uName, $uValue)
    {
        if (!isset($this->entityDefinition['fields'][$uName])) {
            return;
        }

        $this->fields[$uName] = $uValue;
    }

    /**
     * @ignore
     */
    public function insert()
    {
        return $this->db->createQuery()
                ->setTable($this->entityName)
                ->setFields($this->fields)
                ->insert()
                ->execute();
    }

    /**
     * @ignore
     */
    public function update()
    {
        return $this->db->createQuery()
                ->setTable($this->entityName)
                ->setFields($this->fields)
        // ->setWhere()
                ->setLimit(1)
                ->update()
                ->execute();
    }

    /**
     * @ignore
     */
    public function delete()
    {
        return $this->db->createQuery()
                ->setTable($this->entityName)
        // ->setWhere()
                ->setLimit(1)
                ->delete()
                ->execute();
    }

    /**
     * @ignore
     */
    public function ddlCreateSql()
    {
        $tSql = 'CREATE TABLE ' . $this->entityDefinition['name'] . ' (
id UUID NOT NULL,
createdate DATETIME NOT NULL,
updatedate DATETIME NOT NULL,
deletedate DATETIME,';

        if (isset($this->entityDefinition['fieldList'])) {
            foreach ($this->entityDefinition['fieldList'] as $tField) {
                $tSql .= '
' . $tField['name'] . ' ' . strtoupper($tField['type']) . ' NOT NULL,';
            }
        }

            $tSql .= '
PRIMARY KEY(id)
)';

        return $tSql;
    }
}
