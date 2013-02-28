<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Fb;

/**
 * Facebook Query Object Class
 *
 * @package Scabbia
 * @subpackage ExtensibilityExtensions
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
