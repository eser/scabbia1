<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Objects;

/**
 * Objects Extension: Collection Class
 *
 * @package Scabbia
 * @subpackage Objects
 * @version 1.1.0
 */
class Collection implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @ignore
     */
    public $_items;


    /**
     * @ignore
     */
    public function __construct(array $uArray = array())
    {
        $this->_items = $uArray;
    }

    /**
     * @ignore
     */
    public function add($uKey, $uItem = null)
    {
        if (func_num_args() > 1) {
            $this->_items[$uKey] = $uItem;
            return;
        }

        $this->_items[] = $uKey;
    }

    /**
     * @ignore
     */
    public function addUnique($uKey, $uItem = null)
    {
        if (func_num_args() > 1) {
            if (array_key_exists($uKey, $this->_items)) {
                return false;
            }

            $this->_items[$uKey] = $uItem;
            return true;
        }

        if (in_array($uKey, $this->_items, true)) {
            return false;
        }

        $this->_items[] = $uKey;
        return true;
    }

    /**
     * @ignore
     */
    public function addRange($uItems)
    {
        $this->_items += $uItems;
    }

    /**
     * @ignore
     */
    public function keyExists($uKey)
    {
        return array_key_exists($uKey, $this->_items);
    }

    /**
     * @ignore
     */
    public function contains($uItem)
    {
        return in_array($uItem, $this->_items, true);
    }

    /**
     * @ignore
     */
    public function count($uItem = null)
    {
        if (func_num_args() > 1) {
            $tCounted = 0;
            foreach ($this->_items as $tItem) {
                if ($uItem === $tItem) {
                    ++$tCounted;
                }
            }

            return $tCounted;
        }

        return count($this->_items);
    }

    /**
     * @ignore
     */
    public function get($uKey)
    {
        if (!array_key_exists($uKey, $this->_items) && func_num_args() > 1) {
            return func_get_arg(1);
        }

        return $this->_items[$uKey];
    }

    /**
     * @ignore
     */
    public function getRange(array $uArray)
    {
        $tItems = array();

        foreach ($uArray as $tKey => $tItem) {
            if (in_array($tKey, $uArray, true)) {
                continue;
            }

            $tItems[$tKey] = $tItem;
        }

        return $tItems;
    }

    /**
     * @ignore
     */
    public function set($uKey, $uValue)
    {
        $this->_items[$uKey] = $uValue;
    }

    /**
     * @ignore
     */
    public function remove($uItem, $uLimit = 0)
    {
        $tRemoved = 0;

        while (($uLimit === 0 || $tRemoved < $uLimit) && ($tKey = array_search($uItem, $this->_items, true)) !== false) {
            unset($this->_items[$tKey]);
        }

        return $tRemoved;
    }

    /**
     * @ignore
     */
    public function removeKey($uKey)
    {
        if (!isset($this->_items[$uKey])) {
            return 0;
        }

        unset($this->_items[$uKey]);
        return 1;
    }

    /**
     * @ignore
     */
    public function removeIndex($uIndex)
    {
        if ($uIndex >= count($this->_items)) {
            return 0;
        }

        for ($i = 0, reset($this->_items); $i < $uIndex; $i++, next($this->_items)) {
        }

        unset($this->_items[key($this->_items)]);
        return 1;
    }

    /**
     * @ignore
     */
    public function pop()
    {
        return array_pop($this->_items);
    }

    /**
     * @ignore
     */
    public function push($uItem)
    {
        $this->_items[] = $uItem;
    }

    /**
     * @ignore
     */
    public function shift()
    {
        return array_shift($this->_items);
    }

    /**
     * @ignore
     */
    public function unshift($uItem)
    {
        array_unshift($this->_items, $uItem);
    }

    /**
     * @ignore
     */
    public function first()
    {
        reset($this->_items);

        return $this->current();
    }

    /**
     * @ignore
     */
    public function last()
    {
        return end($this->_items);
    }

    /**
     * @ignore
     */
    public function current()
    {
        $tValue = current($this->_items);

        if ($tValue === false) {
            return null;
        }

        return $tValue;
    }

    /**
     * @ignore
     */
    public function next()
    {
        $tValue = $this->current();
        next($this->_items);

        return $tValue;
    }

    /**
     * @ignore
     */
    public function clear()
    {
        $this->_items = array();
        // $this->internalIterator->rewind();
    }

    /**
     * @ignore
     */
    public function walk(/* callable */ $uCallback, $uRecursive = false)
    {
        if ($uRecursive) {
            array_walk_recursive($this->_items, $uCallback);
            return;
        }

        array_walk($this->_items, $uCallback);
    }

    // for array access, $items
    /**
     * @ignore
     */
    public function offsetExists($uId)
    {
        return $this->keyExists($uId);
    }

    /**
     * @ignore
     */
    public function offsetGet($uId)
    {
        return $this->get($uId);
    }

    /**
     * @ignore
     */
    public function offsetSet($uId, $uValue)
    {
        $this->set($uId, $uValue);
    }

    /**
     * @ignore
     */
    public function offsetUnset($uId)
    {
        $this->removeKey($uId);
    }

    // for iteration access
    /**
     * @ignore
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_items);
    }

    /**
     * @ignore
     */
    public function toCollection()
    {
        return new static($this->_items);
    }

    /**
     * @ignore
     */
    public function toArray()
    {
        return $this->_items;
    }

    /**
     * @ignore
     */
    public function &toArrayRef()
    {
        return $this->_items;
    }

    /**
     * @ignore
     */
    public function toString($uSeparator = "")
    {
        return implode($uSeparator, $this->_items);
    }
}
