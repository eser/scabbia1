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
    public $_updateFunc;


    /**
     * @ignore
     */
    public function __construct(/* callable */ $uUpdateFunc, array $uArray = array())
    {
        parent::__construct($uArray);

        $this->_updateFunc = $uUpdateFunc;
    }

    /**
     * @ignore
     */
    public function enqueue($uKey)
    {
        foreach ((array)$uKey as $tKey) {
            if (in_array($tKey, $this->_queue, true) || $this->keyExists($tKey)) {
                continue;
            }

            $this->_queue[] = $tKey;
        }
    }

    /**
     * @ignore
     */
    public function update()
    {
        if ($this->_updateFunc === null) {
            return;
        }

        if (count($this->_queue) == 0) {
            return;
        }

        $this->_items += call_user_func($this->_updateFunc, $this->_queue);
        $this->_queue = array();
    }

    /**
     * @ignore
     */
    public function updateRange(array $uArray)
    {
        $this->enqueue($uArray);
        $this->update();

        return $this->getRange($uArray);
    }

    /**
     * @ignore
     */
    public function get($uKey)
    {
        if (in_array($uKey, $this->_queue, true)) {
            $this->update();
        }

        return call_user_func_array('parent::get', func_get_args());
    }
}
