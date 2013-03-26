<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Database;

use Scabbia\Extensions\Cache\Cache;
use Scabbia\Extensions\Database\Database;
use Scabbia\Extensions\Database\DatabaseQuery;
use Scabbia\Extensions\Database\DatabaseQueryResult;
use Scabbia\Extensions\Database\IQueryGenerator;
use Scabbia\Extensions\Datasources\IDatasource;
use Scabbia\Extensions\Datasources\IServerConnection;
use Scabbia\Extensions\Datasources\ITransactionSupport;
use Scabbia\Extensions\Profiler\Profiler;

/**
 * Database Extension: DatabaseSource Class
 *
 * @package Scabbia
 * @subpackage Database
 * @version 1.1.0
 *
 * @todo generic sqlSelect, sqlUpdate, etc.
 */
abstract class DatabaseSource implements IDatasource, IServerConnection, ITransactionSupport, IQueryGenerator
{
    /**
     * @ignore
     */
    public $id;
    /**
     * @ignore
     */
    public $default;
    /**
     * @ignore
     */
    public $cache = array();
    /**
     * @ignore
     */
    public $inTransaction = false;
    /**
     * @ignore
     */
    public $initCommand;
    /**
     * @ignore
     */
    public $errorHandling = Database::ERROR_NONE;
    /**
     * @ignore
     */
    public $stats = array(
        'query' => 0,
        'cache' => 0
    );


    /**
     * @ignore
     */
    public function __construct(array $uConfig)
    {
        $this->id = $uConfig['id'];
        $this->default = isset($uConfig['default']);

        if (isset($uConfig['initCommand'])) {
            $this->initCommand = $uConfig['initCommand'];
        }
    }

    /**
     * @ignore
     */
    public function connectionOpen()
    {
        if (strlen($this->initCommand) > 0) {
            // $this->execute($this->initCommand); // occurs recursive loop
            //! may need pass the initial command to the profiler extension
            try {
                $this->internalExecute($this->initCommand);
            } catch (\Exception $ex) {
                if ($this->errorHandling == Database::ERROR_EXCEPTION) {
                    throw $ex;
                }

                return false;
            }
        }

        return null;
    }

    /**
     * @ignore
     */
    public function connectionClose()
    {
    }

    /**
     * @ignore
     */
    public function serverInfo() {
    }

    /**
     * @ignore
     */
    public function beginTransaction()
    {
        $this->inTransaction = true;
    }

    /**
     * @ignore
     */
    public function commit()
    {
        $this->inTransaction = false;
    }

    /**
     * @ignore
     */
    public function rollBack()
    {
        $this->inTransaction = false;
    }

    /**
     * @ignore
     */
    public abstract function itSeek($uObject, $uRow);

    /**
     * @ignore
     */
    public abstract function itNext($uObject);

    /**
     * @ignore
     */
    public abstract function itCount($uObject);

    /**
     * @ignore
     */
    public abstract function itClose($uObject);

    /**
     * @ignore
     */
    public abstract function lastInsertId($uName = null);

    /**
     * @ignore
     */
    public abstract function internalExecute($uQuery);

    /**
     * @ignore
     */
    public abstract function queryDirect($uQuery, array $uParameters = array());

    /**
     * @ignore
     */
    public function execute($uQuery)
    {
        $this->connectionOpen();

        Profiler::start(
            'databaseQuery',
            array(
                 'query' => $uQuery,
                 'parameters' => null
            )
        );

        try {
            $tReturn = $this->internalExecute($uQuery);
        } catch (\Exception $ex) {
            if ($this->errorHandling == Database::ERROR_EXCEPTION) {
                throw $ex;
            }

            $tReturn = false;
        }

        Profiler::stop();

        return $tReturn;
    }

    /**
     * @ignore
     */
    public function query($uQuery, array $uParameters = array(), $uCaching = Database::CACHE_MEMORY)
    {
        $this->connectionOpen();

        Profiler::start(
            'databaseQuery',
            array(
                 'query' => $uQuery,
                 'parameters' => $uParameters
            )
        );

        $tFolder = 'database/' . $this->id . '/';

        $uPropsSerialized = hash('adler32', $uQuery);
        foreach ($uParameters as $tProp) {
            $uPropsSerialized .= '_' . $tProp;
        }

        if (($uCaching & Database::CACHE_MEMORY) > 0 && isset($this->cache[$uPropsSerialized])) {
            $tData = $this->cache[$uPropsSerialized]->resume($this);
            $tLoadedFromCache = true;
        } else {
            if (($uCaching & Database::CACHE_FILE) > 0) { //  && Framework::$development <= 0
                $tData = Cache::fileGet($tFolder, $uPropsSerialized, -1, true);

                if ($tData !== false) {
                    $this->cache[$uPropsSerialized] = $tData->resume($this);
                    $tLoadedFromCache = true;
                } else {
                    $tLoadedFromCache = false;
                }
            } else {
                if (($uCaching & Database::CACHE_STORAGE) > 0) { //  && Framework::$development <= 0
                    $tKey = strtr($tFolder, '/', '_') . $uPropsSerialized;
                    $tData = Cache::storageGet($tKey);

                    if ($tData !== false) {
                        $this->cache[$uPropsSerialized] = $tData->resume($this);
                        $tLoadedFromCache = true;
                    } else {
                        $tLoadedFromCache = false;
                    }
                } else {
                    $tData = false;
                    $tLoadedFromCache = false;
                }
            }
        }

        if ($tData === false) {
            $tData = new DatabaseQueryResult($uQuery, $uParameters, $this, $uCaching, $tFolder, $uPropsSerialized);
            ++$this->stats['query'];
        } else {
            ++$this->stats['cache'];
        }

        //! affected rows
        Profiler::stop(
            array(
                 'affectedRows' => $tData->count(),
                 'fromCache' => $tLoadedFromCache
            )
        );

        return $tData;
    }

    /**
     * @ignore
     */
    public function dataset()
    {
        $this->connectionOpen();

        $uProps = func_get_args();
        $uDataset = Database::getDataset(array_shift($uProps));

        if ($uDataset->transaction) {
            $this->beginTransaction();
        }

        try {
            $tCount = 0;
            $tArray = array();

            foreach ($uDataset->parameters as $tParam) {
                $tArray[$tParam] = $uProps[$tCount++];
            }

            try {
                $tResult = $this->query($uDataset->queryString, $tArray, true); //! constant
            } catch (\Exception $ex) {
                if ($this->errorHandling == Database::ERROR_EXCEPTION) {
                    throw $ex;
                }

                $tResult = false;
            }

            if ($this->inTransaction) {
                $this->commit();
            }
        } catch (\Exception $ex) {
            if ($this->inTransaction) {
                $this->rollBack();
            }

            throw $ex;
        }

        ++$this->stats['query'];

        if (isset($tResult)) {
            return $tResult;
        }

        return false;
    }

    /**
     * @ignore
     */
    public function createQuery()
    {
        return new DatabaseQuery($this);
    }
}
