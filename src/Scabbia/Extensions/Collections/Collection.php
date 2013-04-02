<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Collections;

/**
 * Collections Extension: Collection Class
 *
 * @package Scabbia
 * @subpackage Collections
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
    public function __construct($tArray = null)
    {
        $this->_items = is_array($tArray) ? $tArray : array();
    }

    /**
     * @ignore
     */
    public function add($uItem)
    {
        $this->_items[] = $uItem;
    }

    /**
     * @ignore
     */
    public function addKey($uKey, $uItem)
    {
        $this->_items[$uKey] = $uItem;
    }

    /**
     * @ignore
     */
    public function addRange($uItems)
    {
        foreach ($uItems as $tItem) { //SPD (array)$uItems cast
            $this->add($tItem);
        }
    }

    /**
     * @ignore
     */
    public function addKeyRange($uItems)
    {
        foreach ($uItems as $tKey => $tItem) { //SPD (array)$uItems cast
            $this->addKey($tKey, $tItem);
        }
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
        foreach ($this->_items as $tItem) {
            if ($uItem == $tItem) {
                return true;
            }
        }

        return false;
    }

    /**
     * @ignore
     */
    public function count($uItem = null)
    {
        if (!isset($uItem)) {
            return count($this->_items);
        }

        $tCounted = 0;
        foreach ($this->_items as $tItem) {
            if ($uItem != $tItem) {
                continue;
            }

            ++$tCounted;
        }

        return $tCounted;
    }

    /**
     * @ignore
     */
    public function countRange($uItems)
    {
        $tCounted = 0;

        foreach ($uItems as $tItem) { //SPD (array)$uItems cast
            $tCounted += $this->count($tItem);
        }

        return $tCounted;
    }

    /**
     * @ignore
     */
    public function remove($uItem, $uLimit = null)
    {
        $tRemoved = 0;

        foreach ($this->_items as $tKey => $tVal) {
            if ($uItem != $tVal) {
                continue;
            }

            ++$tRemoved;
            unset($this->_items[$tKey]);

            if (isset($uLimit) && $uLimit >= $tRemoved) {
                break;
            }
        }

        return $tRemoved;
    }

    /**
     * @ignore
     */
    public function removeRange($uItems, $uLimitEach = null, $uLimitTotal = null)
    {
        $tRemoved = 0;

        foreach ($uItems as $tItem) { //SPD (array)$uItems cast
            $tRemoved += $this->remove($tItem, $uLimitEach);

            if (isset($uLimitTotal) && $uLimitTotal >= $tRemoved) {
                break;
            }
        }

        return $tRemoved;
    }

    /**
     * @ignore
     */
    public function removeKey($uKey)
    {
        if (!$this->keyExists($uKey, true)) {
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
        //! todo: seek with iterator
        if ($this->count < $uIndex) {
            return 0;
        } //SPD (int)$uIndex cast

        reset($this->_items);
        for ($i = 0; $i < $uIndex; $i++) {
            next($this->_items);
        }

        unset($this->_items[key($this->_items)]);

        return 1;
    }

    /**
     * @ignore
     */
    public function chunk($uSize, $uPreserveKeys = false)
    {
        $tArray = array_chunk($this->_items, $uSize, $uPreserveKeys);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function combineKeys($uArray)
    {
        if (is_subclass_of($uArray, 'Scabbia\\Extensions\\Collections\\Collection')) {
            $uArray = $uArray->toArrayRef();
        }

        $tArray = array_combine($uArray, $this->_items);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function combineValues($uArray)
    {
        if (is_subclass_of($uArray, 'Scabbia\\Extensions\\Collections\\Collection')) {
            $uArray = $uArray->toArrayRef();
        }

        $tArray = array_combine($this->_items, $uArray);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function countValues()
    {
        $tArray = array_count_values($this->_items);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function diff()
    {
        $uParms = array(&$this->_items);
        foreach (func_get_args() as $tItem) {
            if (is_subclass_of($tItem, 'Scabbia\\Extensions\\Collections\\Collection')) {
                $uParms[] = $tItem->toArrayRef();
                continue;
            }

            $uParms[] = $tItem;
        }

        $tArray = call_user_func_array('array_diff', $uParms);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function filter($uCallback)
    {
        $tArray = array_filter($this->_items, $uCallback);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function flip()
    {
        $tArray = array_flip($this->_items);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function intersect()
    {
        $uParms = array(&$this->_items);

        foreach (func_get_args() as $tItem) {
            if (is_subclass_of($tItem, 'Scabbia\\Extensions\\Collections\\Collection')) {
                $uParms[] = $tItem->toArrayRef();
                continue;
            }

            $uParms[] = $tItem;
        }

        $tArray = call_user_func_array('array_intersect', $uParms);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function keys()
    {
        $tArray = array_keys($this->_items);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function map($uCallback)
    {
        $tArray = array_map($uCallback, $this->_items);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function mergeRecursive()
    {
        $uParms = array(&$this->_items);

        foreach (func_get_args() as $tItem) {
            if (is_subclass_of($tItem, 'Scabbia\\Extensions\\Collections\\Collection')) {
                $uParms[] = $tItem->toArrayRef();
                continue;
            }

            $uParms[] = $tItem;
        }

        $tArray = call_user_func_array('array_merge_recursive', $uParms);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function merge()
    {
        $uParms = array(&$this->_items);
        foreach (func_get_args() as $tItem) {
            if (is_subclass_of($tItem, 'Scabbia\\Extensions\\Collections\\Collection')) {
                $uParms[] = $tItem->toArrayRef();
                continue;
            }

            $uParms[] = $tItem;
        }

        $tArray = call_user_func_array('array_merge', $uParms);

        return new static($tArray);
    }

    /**
     * @ignore
     */
    public function pad($uSize, $uValue)
    {
        $tArray = array_pad($this->_items, $uSize, $uValue);

        return new static($tArray);
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
    public function product()
    {
        return array_product($this->_items);
    }

    /**
     * @ignore
     */
    public function push()
    {
        $uParms = array(&$this->_items);

        foreach (func_get_args() as $tItem) {
            $uParms[] = $tItem;
        }

        return call_user_func_array('array_push', $uParms);
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
        return $this->_items[$uId];
    }

    /**
     * @ignore
     */
    public function offsetSet($uId, $uValue)
    {
        $this->_items[$uId] = $uValue;
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
        return new Collection($this->_items);
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
    public function toString($uSeparator = '')
    {
        return implode($uSeparator, $this->_items);
    }
}
