<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Database;

use Scabbia\Extensions\Database\Database;
use Scabbia\Extensions\Datasources\Datasources;
use Scabbia\Extensions\Helpers\String;

/**
 * Database Extension: DatabaseQuery Class
 *
 * @package Scabbia
 * @subpackage Database
 * @version 1.1.0
 */
class DatabaseQuery
{
    /**
     * @ignore
     */
    public $database = null;

    /**
     * @ignore
     */
    public $table;
    /**
     * @ignore
     */
    public $fields;
    /**
     * @ignore
     */
    public $rawFields;
    /**
     * @ignore
     */
    public $parameters;
    /**
     * @ignore
     */
    public $where;
    /**
     * @ignore
     */
    public $groupby;
    /**
     * @ignore
     */
    public $orderby;
    /**
     * @ignore
     */
    public $limit;
    /**
     * @ignore
     */
    public $offset;
    /**
     * @ignore
     */
    public $sequence;
    /**
     * @ignore
     */
    public $caching;


    /**
     * @ignore
     */
    public function __construct($uDatabase = null)
    {
        $this->setDatabase($uDatabase);
    }

    /**
     * @ignore
     */
    public function setDatabase($uDatabase = null)
    {
        if ($uDatabase !== null) {
            $this->database = $uDatabase;
        } else {
            $this->database = Datasources::get(); // default
        }

        $this->clear();
    }

    /**
     * @ignore
     */
    public function setDatabaseName($uDatabaseName)
    {
        $this->database = Datasources::get($uDatabaseName);
        $this->clear();
    }

    /**
     * @ignore
     */
    public function clear()
    {
        $this->table = '';
        $this->fields = array();
        $this->rawFields = array();
        $this->parameters = array();
        $this->where = '';
        $this->groupby = '';
        $this->orderby = '';
        $this->limit = -1;
        $this->offset = -1;
        $this->sequence = '';
        $this->returning = '';
        $this->caching = null;
    }

    /**
     * @ignore
     */
    public function setTable($uTableName)
    {
        $this->table = $uTableName;

        return $this;
    }

    /**
     * @ignore
     */
    public function joinTable($uTableName, $uCondition, $uJoinType = 'INNER')
    {
        $this->table .= ' ' . $uJoinType . ' JOIN ' . $uTableName . ' ON ' . $uCondition;

        return $this;
    }

    /**
     * @ignore
     */
    public function setFields($uField, $uValue = false)
    {
        $this->fields = array();
        $this->rawFields = array();

        $this->addField($uField, $uValue);

        return $this;
    }

    /**
     * @ignore
     */
    public function setFieldsDirect($uField, $uValue = false)
    {
        $this->fields = array();
        $this->rawFields = array();

        $this->addFieldDirect($uField, $uValue);

        return $this;
    }

    /**
     * @ignore
     */
    public function addField($uField, $uValue = false)
    {
        if ($uValue === false) {
            if (is_array($uField)) {
                foreach ($uField as $tField => $tValue) {
                    // $this->fields[$tField] = String::squote($tValue, true);
                    if ($tValue === null) {
                        $this->fields[$tField] = 'NULL';
                    } else {
                        $this->fields[$tField] = ':' . $tField;
                        $this->parameters[$this->fields[$tField]] = $tValue;
                    }
                }
            } else {
                $this->rawFields[] = $uField;
            }
        } else {
            if ($uValue === null) {
                $this->fields[$uField] = 'NULL';
            } else {
                // $this->fields[$uField] = String::squote($uValue, true);
                $this->fields[$uField] = ':' . $uField;
                $this->parameters[$this->fields[$uField]] = $uValue;
            }
        }

        return $this;
    }

    /**
     * @ignore
     */
    public function addFieldDirect($uField, $uValue = null)
    {
        if ($uValue === false) {
            if (is_array($uField)) {
                foreach ($uField as $tField => $tValue) {
                    // $this->fields[$tField] = String::squote($tValue, true);
                    if ($tValue === null) {
                        $this->fields[$tField] = 'NULL';
                    } else {
                        $this->fields[$tField] = $tValue;
                    }
                }
            } else {
                $this->rawFields[] = $uField;
            }
        } else {
            if ($uValue === null) {
                $this->fields[$uField] = 'NULL';
            } else {
                $this->fields[$uField] = $uValue;
            }
        }

        return $this;
    }

    /**
     * @ignore
     */
    public function addParameter($uParameter, $uValue)
    {
        $this->parameters[$uParameter] = $uValue;

        return $this;
    }

    /**
     * @ignore
     */
    private function constructWhere(array $uArray, $uIsList = false)
    {
        $tOutput = '(';
        $tPreviousElement = null;

        foreach ($uArray as $tElement) {
            if (is_array($tElement)) {
                $tOutput .= $this->constructWhere($tElement, ($tPreviousElement == _IN || $tPreviousElement == _NOTIN));
                continue;
            }

            if ($uIsList) {
                if ($tPreviousElement !== null) {
                    $tOutput .= ', ' . String::squote($tElement, true);
                } else {
                    $tOutput .= String::squote($tElement, true);
                }
            } else {
                $tOutput .= $tElement;
            }

            $tPreviousElement = $tElement;
        }

        $tOutput .= ')';

        return $tOutput;
    }

    /**
     * @ignore
     */
    public function setWhere($uCondition, $uList = null)
    {
        if (is_array($uCondition)) {
            $this->where = $this->constructWhere($uCondition);

            return $this;
        }

        $this->where = $uCondition;

        if ($uList !== null) {
            $this->where .= ' (' . implode(', ', String::squoteArray($uList, true)) . ')';
        }

        return $this;
    }

    /**
     * @ignore
     */
    public function andWhere($uCondition, $uList = null, $uKeyword = 'OR')
    {
        if (is_array($uCondition)) {
            if (count($uCondition) > 0) {
                if (strlen($this->where) > 0) {
                    $this->where .= ' AND ';
                }

                $this->where .= '(' . implode(' ' . $uKeyword . ' ', $uCondition) . ')';
            }
        } else {
            if (strlen($this->where) > 0) {
                $this->where .= ' AND ';
            }

            $this->where .= $uCondition;

            if ($uList !== null) {
                $this->where .= ' (' . implode(', ', String::squoteArray($uList, true)) . ')';
            }
        }

        return $this;
    }

    /**
     * @ignore
     */
    public function orWhere($uCondition, $uList = null, $uKeyword = 'AND')
    {
        if (is_array($uCondition)) {
            if (count($uCondition) > 0) {
                if (strlen($this->where) > 0) {
                    $this->where .= ' OR ';
                }

                $this->where .= '(' . implode(' ' . $uKeyword . ' ', $uCondition) . ')';
            }
        } else {
            if (strlen($this->where) > 0) {
                $this->where .= ' OR ';
            }

            $this->where .= $uCondition;

            if ($uList !== null) {
                $this->where .= ' (' . implode(', ', String::squoteArray($uList, true)) . ')';
            }
        }

        return $this;
    }

    /**
     * @ignore
     */
    public function setGroupBy($uGroupBy)
    {
        $this->groupby = $uGroupBy;

        return $this;
    }

    /**
     * @ignore
     */
    public function addGroupBy($uGroupBy)
    {
        $this->groupby .= ', ' . $uGroupBy;

        return $this;
    }

    /**
     * @ignore
     */
    public function setOrderBy($uOrderBy, $uOrder = null)
    {
        $this->orderby = $uOrderBy;
        if ($uOrder !== null) {
            $this->orderby .= ' ' . $uOrder;
        }

        return $this;
    }

    /**
     * @ignore
     */
    public function addOrderBy($uOrderBy, $uOrder = null)
    {
        $this->orderby .= ', ' . $uOrderBy;
        if ($uOrder !== null) {
            $this->orderby .= ' ' . $uOrder;
        }

        return $this;
    }

    /**
     * @ignore
     */
    public function setLimit($uLimit)
    {
        $this->limit = $uLimit;

        return $this;
    }

    /**
     * @ignore
     */
    public function setOffset($uOffset)
    {
        $this->offset = $uOffset;

        return $this;
    }

    /**
     * @ignore
     */
    public function setSequence($uSequence)
    {
        $this->sequence = $uSequence;

        return $this;
    }

    /**
     * @ignore
     */
    public function setReturning($uReturning)
    {
        $this->returning = $uReturning;

        return $this;
    }

    /**
     * @ignore
     */
    public function setCaching($uCaching)
    {
        $this->caching = $uCaching;

        return $this;
    }

    /**
     * @ignore
     */
    public function insertQuery($uReturn = true)
    {
        $tQuery = $this->database->sqlInsert(
            $this->table,
            $this->fields,
            $this->returning
        );

        if (!$uReturn) {
            echo $tQuery;
            return $this;
        }

        return $tQuery;
    }

    /**
     * @ignore
     */
    public function insert()
    {
        $tReturn = $this->database->query(
            $this->insertQuery(),
            $this->parameters,
            $this->caching,
            true
        );

        if ($this->sequence !== null && strlen($this->sequence) > 0) {
            $tReturn->_lastInsertId = $this->database->lastInsertId($this->sequence);
        } else {
            $tReturn->_lastInsertId = $this->database->lastInsertId();
        }

        $this->clear();

        return $tReturn;
    }

    /**
     * @ignore
     */
    public function updateQuery($uReturn = true)
    {
        $tQuery = $this->database->sqlUpdate(
            $this->table,
            $this->fields,
            $this->rawFields,
            $this->where,
            array('limit' => $this->limit)
        );

        if (!$uReturn) {
            echo $tQuery;
            return $this;
        }

        return $tQuery;
    }

    /**
     * @ignore
     */
    public function update()
    {
        $tReturn = $this->database->query(
            $this->updateQuery(),
            $this->parameters,
            $this->caching,
            true
        );

        $this->clear();

        return $tReturn;
    }

    /**
     * @ignore
     */
    public function deleteQuery($uReturn = true)
    {
        $tQuery = $this->database->sqlDelete(
            $this->table,
            $this->where,
            array('limit' => $this->limit)
        );

        if (!$uReturn) {
            echo $tQuery;
            return $this;
        }

        return $tQuery;
    }

    /**
     * @ignore
     */
    public function delete()
    {
        $tReturn = $this->database->query(
            $this->deleteQuery(),
            $this->parameters,
            $this->caching,
            true
        );

        $this->clear();

        return $tReturn;
    }

    /**
     * @ignore
     */
    public function getQuery($uReturn = true)
    {
        $tQuery = $this->database->sqlSelect(
            $this->table,
            $this->fields,
            $this->rawFields,
            $this->where,
            $this->orderby,
            $this->groupby,
            array('limit' => $this->limit, 'offset' => $this->offset)
        );

        if (!$uReturn) {
            echo $tQuery;
            return $this;
        }

        return $tQuery;
    }

    /**
     * @ignore
     */
    public function get()
    {
        $tReturn = $this->database->query(
            $this->getQuery(),
            $this->parameters,
            $this->caching,
            false
        );

        $this->clear();

        return $tReturn;
    }

    /**
     * @ignore
     */
    public function aggregateQuery($uOperation = 'COUNT', $uReturn = true) {
        $tQuery = $this->database->sqlSelect(
            $this->table,
            array(),
            $uOperation . '(' . $this->rawFields . ')',
            $this->where,
            null,
            $this->groupby
        );

        if (!$uReturn) {
            echo $tQuery;
            return $this;
        }

        return $tQuery;
    }

    /**
     * @ignore
     */
    public function aggregate($uOperation = 'COUNT')
    {
        $tReturn = $this->database->query(
            $this->aggregateQuery($uOperation),
            $this->parameters,
            $this->caching,
            false
        );

        $this->clear();

        return $tReturn->scalar();
    }

    /**
     * @ignore
     */
    public function runSmartObject($uInstance)
    {
        $tRow = $this->getRow();

        foreach ($tRow as $tKey => $tValue) {
            $uInstance->obtained[$tKey] = $tValue;
        }
    }
}
