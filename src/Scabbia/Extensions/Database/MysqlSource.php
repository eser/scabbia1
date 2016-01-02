<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Database;

use Scabbia\Extensions\Database\DatabaseSource;

/**
 * Database Extension: MysqlSource Class
 *
 * @package Scabbia
 * @subpackage Database
 * @version 1.1.0
 *
 * @todo unbuffered queries
 */
class MysqlSource extends DatabaseSource
{
    /**
     * @ignore
     */
    public static $type = 'mysql';


    /**
     * @ignore
     */
    public $connection = null;
    /**
     * @ignore
     */
    public $host;
    /**
     * @ignore
     */
    public $database;
    /**
     * @ignore
     */
    public $username;
    /**
     * @ignore
     */
    public $password;
    /**
     * @ignore
     */
    public $persistent;


    /**
     * @ignore
     */
    public function __construct(array $uConfig)
    {
        parent::__construct($uConfig);

        $this->host = $uConfig['host'];
        $this->database = $uConfig['database'];
        $this->username = $uConfig['username'];
        $this->password = $uConfig['password'];

        $this->persistent = (isset($uConfig['persistent']) && $uConfig['persistent'] === true);
    }

    /**
     * @ignore
     */
    public function connectionOpen()
    {
        if ($this->connection !== null) {
            return;
        }

        // if ($this->persistent) {
        // }

        \mysqli_report(\MYSQLI_REPORT_STRICT); // mysqli_sql_exception
        $this->connection = new \mysqli($this->host, $this->username, $this->password, $this->database);

        /*
        if ($this->connection->connect_errno > 0) {
            throw new \Exception('Mysql Exception: ' . $this->connection->connect_error);
        }
        */

        parent::connectionOpen();
    }

    /**
     * @ignore
     */
    public function connectionClose()
    {
        parent::connectionClose();
    }

    /**
     * @ignore
     */
    public function serverInfo()
    {
        return $this->connection->server_info;
    }

    /**
     * @ignore
     */
    public function beginTransaction()
    {
        parent::beginTransaction();

        $this->connection->autocommit(false);
    }

    /**
     * @ignore
     */
    public function commit()
    {
        $this->connection->commit();

        parent::commit();
    }

    /**
     * @ignore
     */
    public function rollBack()
    {
        $this->connection->rollback();

        parent::rollBack();
    }

    /**
     * @ignore
     */
    public function sqlInsert($uTable, $uObject, $uReturning = "")
    {
        $tSql =
            'INSERT INTO ' . $uTable . ' ('
                . implode(', ', array_keys($uObject))
                . ') VALUES ('
                . implode(', ', array_values($uObject))
                . ')';

        // if (strlen($uReturning) > 0) {
        //     $tSql .= ' RETURNING ' . $uReturning;
        // }

        return $tSql;
    }

    /**
     * @ignore
     */
    public function sqlUpdate($uTable, $uObject, $uRawObject, $uWhere, $uExtra = null)
    {
        $tPairs = $uRawObject;
        foreach ($uObject as $tKey => $tValue) {
            $tPairs[] = $tKey . '=' . $tValue;
        }

        $tSql = 'UPDATE ' . $uTable . ' SET '
            . implode(', ', $tPairs);

        if (strlen($uWhere) > 0) {
            $tSql .= ' WHERE ' . $uWhere;
        }

        if ($uExtra !== null) {
            if (isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
                $tSql .= ' LIMIT ' . $uExtra['limit'];
            }
        }

        return $tSql;
    }

    /**
     * @ignore
     */
    public function sqlDelete($uTable, $uWhere, $uExtra = null)
    {
        $tSql = 'DELETE FROM ' . $uTable;

        if (strlen($uWhere) > 0) {
            $tSql .= ' WHERE ' . $uWhere;
        }

        if ($uExtra !== null) {
            if (isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
                $tSql .= ' LIMIT ' . $uExtra['limit'];
            }
        }

        return $tSql;
    }

    /**
     * @ignore
     */
    public function sqlSelect($uTable, $uFields, $uRawFields, $uWhere, $uOrderBy, $uGroupBy, $uExtra = null)
    {
        $tSql = 'SELECT ';

        if (count($uFields) > 0 || count($uRawFields) > 0) {
            $tSql .= implode(', ', $uFields);
            $tSql .= implode(', ', $uRawFields);
        } else {
            $tSql .= '*';
        }

        $tSql .= ' FROM ' . $uTable;

        if (strlen($uWhere) > 0) {
            $tSql .= ' WHERE ' . $uWhere;
        }

        if ($uGroupBy !== null && strlen($uGroupBy) > 0) {
            $tSql .= ' GROUP BY ' . $uGroupBy;
        }

        if ($uOrderBy !== null && strlen($uOrderBy) > 0) {
            $tSql .= ' ORDER BY ' . $uOrderBy;
        }

        if ($uExtra !== null) {
            if (isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
                if (isset($uExtra['offset']) && $uExtra['offset'] >= 0) {
                    $tSql .= ' LIMIT ' . $uExtra['offset'] . ', ' . $uExtra['limit'];
                } else {
                    $tSql .= ' LIMIT ' . $uExtra['limit'];
                }
            }
        }

        return $tSql;
    }

    /**
     * @ignore
     */
    public function itSeek($uObject, $uRow)
    {
        $uObject->data_seek($uRow);

        return $this->itNext($uObject);
    }

    /**
     * @ignore
     */
    public function itNext($uObject)
    {
        return $uObject->fetch();
    }

    /**
     * @ignore
     */
    public function itCount($uObject)
    {
        return $uObject->num_rows;
    }

    /**
     * @ignore
     */
    public function itClose($uObject)
    {
        return $uObject->close();
    }

    /**
     * @ignore
     */
    public function lastInsertId($uName = null)
    {
        return $this->connection->insert_id;
    }

    /**
     * @ignore
     */
    public function internalExecute($uQuery)
    {
        return $this->connection->query($uQuery);
    }

    /**
     * @ignore
     */
    public function queryDirect($uQuery, array $uParameters = array())
    {
        $tQuery = $this->connection->prepare($uQuery);

        foreach ($uParameters as $tParameter) {
            $tType = gettype($tParameter);
            if ($tType === 'integer') {
                $tType = 'i';
            } elseif ($tType === 'double') {
                $tType = 'd';
            } else {
                $tType = 's';
            }

            $tQuery->bind_param($tType, $tParameter);
        }

        $tQuery->execute();

        return $tQuery;
    }

    /**
     * @ignore
     */
    public function queryArray($uQuery, array $uParameters = array())
    {
        $tQuery = $this->queryDirect($uQuery, $uParameters);
        return $tQuery->fetch_all();
    }
}
