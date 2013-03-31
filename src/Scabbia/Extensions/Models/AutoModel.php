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

        switch ($tReturn['method']['type']) {
            /*
            case 'add':
                $tQuery->setFields($this->fields)
                    ->insert()
                    ->execute();
                break;
            case 'edit':
                $tQuery->setFields($this->fields)
                    // ->setWhere()
                    ->setLimit(1)
                    ->update()
                    ->execute();
                break;
            case 'delete':
                $tQuery->setLimit(1)
                    ->delete()
                    ->execute();
                break;
            case 'view':
                break;
            */
            case 'list':
                $tReturn['rows'] = $tQuery->setFieldsDirect($tReturn['method']['fields'])
                    // ->setWhere()
                    ->get()
                    ->all();
                break;
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
