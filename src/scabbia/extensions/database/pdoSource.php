<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Database;

use Scabbia\Extensions\Database\DatabaseSource;

/**
 * Database Extension: PdoSource Class
 *
 * @package Scabbia
 * @subpackage Database
 * @version 1.1.0
 */
class PdoSource extends DatabaseSource
{
    /**
     * @ignore
     */
    public static $type = 'pdo';


    /**
     * @ignore
     */
    public $connection = null;
    /**
     * @ignore
     */
    public $standard = null;
    /**
     * @ignore
     */
    public $pdoString;
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
    public $overrideCase;
    /**
     * @ignore
     */
    public $persistent;
    /**
     * @ignore
     */
    public $fetchMode;


    /**
     * @ignore
     */
    public function __construct(array $uConfig)
    {
        parent::__construct($uConfig);

        $this->pdoString = $uConfig['pdoString'];
        $this->username = $uConfig['username'];
        $this->password = $uConfig['password'];

        if (isset($uConfig['overrideCase'])) {
            $this->overrideCase = $uConfig['overrideCase'];
        }

        $this->persistent = (isset($uConfig['persistent']) && $uConfig['persistent'] === true);
        $this->fetchMode = \PDO::FETCH_ASSOC;

        $tConnectionString = explode(':', $this->pdoString, 2);
        $this->standard = $tConnectionString[0];
    }

    /**
     * @ignore
     */
    public function connectionOpen()
    {
        if ($this->connection !== null) {
            return;
        }

        $tParms = array();
        if ($this->persistent) {
            $tParms[\PDO::ATTR_PERSISTENT] = true;
        }

        if ($this->overrideCase === 'lower') {
            $tParms[\PDO::ATTR_CASE] = \PDO::CASE_LOWER;
        } elseif ($this->overrideCase === 'upper') {
            $tParms[\PDO::ATTR_CASE] = \PDO::CASE_UPPER;
        } else {
            $tParms[\PDO::ATTR_CASE] = \PDO::CASE_NATURAL;
        }

        $tParms[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;

        $this->connection = new \PDO($this->pdoString, $this->username, $this->password, $tParms);

        // $this->standard = $this->connection->getAttribute(\PDO::ATTR_DRIVER_NAME);

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
        return $this->connection->getAttribute(\PDO::ATTR_SERVER_INFO);
    }

    /**
     * @ignore
     */
    public function beginTransaction()
    {
        parent::beginTransaction();

        if ($this->transactionLevel === 1) {
            $this->connection->beginTransaction();
        } else {
            $this->connection->exec('SAVEPOINT LEVEL' . $this->transactionLevel);
        }
    }

    /**
     * @ignore
     */
    public function commit()
    {
        if ($this->transactionLevel === 1) {
            $this->connection->commit();
        } else {
            $this->connection->exec('RELEASE SAVEPOINT LEVEL' . $this->transactionLevel);
        }

        parent::commit();
    }

    /**
     * @ignore
     */
    public function rollBack()
    {
        if ($this->transactionLevel === 1) {
            $this->connection->rollBack();
        } else {
            $this->connection->exec('ROLLBACK TO SAVEPOINT LEVEL' . $this->transactionLevel);
        }

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

        if (strlen($uReturning) > 0) {
            $tSql .= ' RETURNING ' . $uReturning;
        }

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
            if ($this->standard === 'mysql') {
                if (isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
                    $tSql .= ' LIMIT ' . $uExtra['limit'];
                }
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
            if ($this->standard === 'mysql') {
                if (isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
                    $tSql .= ' LIMIT ' . $uExtra['limit'];
                }
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
            if ($this->standard === 'pgsql') {
                if (isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
                    $tSql .= ' LIMIT ' . $uExtra['limit'];
                }

                if (isset($uExtra['offset']) && $uExtra['offset'] >= 0) {
                    $tSql .= ' OFFSET ' . $uExtra['offset'];
                }
            } else {
                if ($this->standard === 'mysql') {
                    if (isset($uExtra['limit']) && $uExtra['limit'] >= 0) {
                        if (isset($uExtra['offset']) && $uExtra['offset'] >= 0) {
                            $tSql .= ' LIMIT ' . $uExtra['offset'] . ', ' . $uExtra['limit'];
                        } else {
                            $tSql .= ' LIMIT ' . $uExtra['limit'];
                        }
                    }
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
        // return $uObject->fetch($this->fetchMode, \PDO::FETCH_ORI_ABS, $uRow);
        for ($i = 0; $i < $uRow; $i++) {
            $uObject->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT);
        }

        return $this->itNext($uObject);
    }

    /**
     * @ignore
     */
    public function itNext($uObject)
    {
        return $uObject->fetch($this->fetchMode, \PDO::FETCH_ORI_NEXT);
    }

    /**
     * @ignore
     */
    public function itCount($uObject)
    {
        return $uObject->rowCount();
    }

    /**
     * @ignore
     */
    public function itClose($uObject)
    {
        return $uObject->closeCursor();
    }

    /**
     * @ignore
     */
    public function lastInsertId($uName = null)
    {
        return $this->connection->lastInsertId($uName);
    }

    /**
     * @ignore
     */
    public function internalExecute($uQuery)
    {
        return $this->connection->exec($uQuery);
    }

    /**
     * @ignore
     */
    public function queryDirect($uQuery, array $uParameters = array())
    {
        $tQuery = $this->connection->prepare($uQuery);
        $tQuery->execute($uParameters);

        return $tQuery;
    }

    /**
     * @ignore
     */
    public function queryArray($uQuery, array $uParameters = array())
    {
        $tQuery = $this->queryDirect($uQuery, $uParameters);
        return $tQuery->fetchAll(\PDO::FETCH_ASSOC);
    }
}
