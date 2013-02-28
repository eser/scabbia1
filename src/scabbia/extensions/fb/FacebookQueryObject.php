<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Fb;

/**
 * Fb Extension: FacebookQueryObject Class
 *
 * @package Scabbia
 * @subpackage Fb
 * @version 1.1.0
 */
class FacebookQueryObject
{
    /**
     * @ignore
     */
    public $object;
    /**
     * @ignore
     */
    public $data;
    /**
     * @ignore
     */
    public $hasPreviousPage;
    /**
     * @ignore
     */
    public $hasNextPage;


    /**
     * @param $uObject
     */
    public function __construct($uObject)
    {
        $this->object = $uObject;
        $this->data = (isset($this->object['data']) ? $this->object['data'] : null);
        $this->hasPreviousPage = (isset($this->object['paging']) && isset($this->object['paging']['previous']));
        $this->hasNextPage = (isset($this->object['paging']) && isset($this->object['paging']['next']));
    }
}
