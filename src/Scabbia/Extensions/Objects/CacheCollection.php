<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Objects;

use Scabbia\Extensions\Objects\Collection;

/**
 * Objects Extension: CacheCollection Class
 *
 * @package Scabbia
 * @subpackage Objects
 * @version 1.1.0
 */
class CacheCollection extends Collection
{
    /**
     * @ignore
     */
    public $_queue = array();
    /**
     * @ignore
     */
    public $_prefetch = null;
    /**
     * @ignore
     */
    public $_fetchAll = null;


    /**
     * @ignore
     */
    public function addToQueue($uKey)
    {
        if (in_array($uKey, $this->_queue, true) || $this->keyExists($uKey)) {
            return;
        }

        $this->_queue[] = $uKey;
    }

    /**
     * @ignore
     */
    public function prefetch()
    {
        if (is_null($this->_prefetch)) {
            return;
        }

        call_user_func($this->_prefetch);
    }

    /**
     * @ignore
     */
    public function fetchAll()
    {
        if (is_null($this->_fetchAll)) {
            return;
        }

        $this->_items += call_user_func($this->_fetchAll, $this->_queue);
        $this->_queue = array();
    }
}
