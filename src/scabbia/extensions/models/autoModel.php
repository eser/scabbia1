<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
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
    public $modelDefinition;
    /**
     * @ignore
     */
    public $methods = array();


    /**
     * @ignore
     */
    public function __construct($uModelName, $uDatasource = null)
    {
        parent::__construct($uDatasource);

        $this->entityName = $uModelName;
        $this->modelDefinition = AutoModels::get($uModelName);
    }

    /**
     * @ignore
     */
    public function call($uMethod)
    {
        if (!isset($this->modelDefinition['methodList'][$uMethod])) {
            return false;
        }

        $tReturn = array(
            'method' => $this->modelDefinition['methodList'][$uMethod]
        );

        $tQuery = $this->db->createQuery()
                ->setTable($this->entityName);

        /* if ($tReturn['method']['type'] === 'add') {
            $tQuery->setFields($this->fields)
                ->insert()
                ->execute();
        } elseif ($tReturn['method']['type'] === 'edit') {
            $tQuery->setFields($this->fields)
                // ->setWhere()
                ->setLimit(1)
                ->update()
                ->execute();
        } elseif ($tReturn['method']['type'] === 'delete') {
            $tQuery->setLimit(1)
                ->delete()
                ->execute();
        } elseif ($tReturn['method']['type'] === 'view') {
        } else */
        if ($tReturn['method']['type'] === 'list') {
            $tReturn['rows'] = $tQuery->setFieldsDirect($tReturn['method']['fields'])
                // ->setWhere()
                ->get()
                ->all();
        }

        return $tReturn;
    }

    /**
     * @ignore
     */
    public function ddlCreateSql()
    {
        $tSql = 'CREATE TABLE ' . $this->modelDefinition['name'] . ' (';

        if (isset($this->modelDefinition['fieldList'])) {
            foreach ($this->modelDefinition['fieldList'] as $tField) {
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
