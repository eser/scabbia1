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
use Scabbia\Extensions\Database\Datasets;
use Scabbia\Extensions\Datasources\Datasource;
use Scabbia\Extensions\Profiler\Profiler;
use Scabbia\Extensions;

/**
 * Database Extension: DatabaseConnection Class
 *
 * @package Scabbia
 * @subpackage Database
 * @version 1.1.0
 */
class DatabaseConnection extends Datasource
{
    /**
     * @ignore
     */
    public $default;
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
    public function __construct($uConfig)
    {
        parent::__construct($uConfig);

        $this->default = isset($uConfig['default']);

        if (isset($uConfig['initCommand'])) {
            $this->initCommand = $uConfig['initCommand'];
        }
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * @ignore
     */
    public function open()
    {
        parent::open();

        if (strlen($this->initCommand) > 0) {
            // $this->execute($this->initCommand); // occurs recursive loop
            //! may need pass the initial command to the profiler extension
            try {
                $this->provider->execute($this->initCommand);
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
    public function close()
    {
        parent::close();
    }

    /**
     * @ignore
     */
    public function beginTransaction()
    {
        $this->open();
        $this->provider->beginTransaction();
        $this->inTransaction = true;
    }

    /**
     * @ignore
     */
    public function commit()
    {
        $this->provider->commit();
        $this->inTransaction = false;
    }

    /**
     * @ignore
     */
    public function rollBack()
    {
        $this->provider->rollBack();
        $this->inTransaction = false;
    }

    /**
     * @ignore
     */
    public function execute($uQuery)
    {
        $this->open();

        Profiler::start(
            'databaseQuery',
            array(
                 'query' => $uQuery,
                 'parameters' => null
            )
        );

        try {
            $tReturn = $this->provider->execute($uQuery);
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
    public function query($uQuery, $uParameters = array(), $uCaching = Database::CACHE_MEMORY)
    {
        $this->open();

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
    public function lastInsertId($uName = null)
    {
        return $this->provider->lastInsertId($uName);
    }

    /**
     * @ignore
     */
    public function serverInfo()
    {
        return parent::serverInfo();
    }

    /**
     * @ignore
     */
    public function dataset()
    {
        $this->open();

        $uProps = func_get_args();
        $uDataset = Datasets::get(array_shift($uProps));

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
