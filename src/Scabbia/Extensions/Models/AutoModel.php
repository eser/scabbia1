<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Models;

use Scabbia\Extensions\Models\AutoModels;
use Scabbia\Extensions\Models\Model;

/**
 * Models Extension: AutoModel Class
 *
 * @package Scabbia
 * @subpackage Models
 * @version 1.1.0
 */
class AutoModel extends Model
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
    public function __construct($uEntityName, $uDatasource = null)
    {
        parent::__construct($uDatasource);

        $this->entityName = $uEntityName;
        $this->entityDefinition = AutoModels::get($uEntityName);
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
    public function getAll()
    {
	    $tFields = $this->ddlGetFieldsForMethod('list', 'name');

        return $this->db->createQuery()
            ->setTable($this->entityName)
            ->setFieldsDirect($tFields)
            // ->setWhere()
            ->get()
            ->all();
    }

	/**
	 * @ignore
	 */
	public function ddlGetFieldsForMethod($uMethod, $uProperty = null)
	{
		$tMethods = array();

		foreach ($this->entityDefinition['fieldList'] as $tField) {
			if (isset($tField['methods']) && in_array($uMethod, $tField['methods'], true)) {
				if (!is_null($uProperty)) {
					$tMethods[] = $tField[$uProperty];
					continue;
				}

				$tMethods[] = $tField;
			}
		}

		return $tMethods;
	}

    /**
     * @ignore
     */
    public function ddlCreateSql()
    {
        $tSql = 'CREATE TABLE ' . $this->entityDefinition['name'] . ' (';

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
