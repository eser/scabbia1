<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

/**
 * Custom exception class
 *
 * @package Scabbia
 * @version 1.1.0
 */
class CustomException extends \Exception
{
    /**
     * @var string   Type of exception
     */
    public $type;
    /**
     * @var string   Title
     */
    public $title;


    /**
     * Constructs a new delegate in order to assign it to a member
     *
     * @param string     $uType
     * @param int        $uTitle
     * @param string     $uMessage
     * @param \Exception $uPrevious
     */
    public function __construct($uType, $uTitle, $uMessage, \Exception $uPrevious = null)
    {
        $this->type = $uType;
        $this->title = $uTitle;

        parent::__construct($uMessage, 0, $uPrevious);
    }

    /**
     * String representation of the exception.
     *
     * @return string the string representation of the exception
     */
    public function __toString()
    {
        return get_class($this) . ' [' . $this->type . ']: ' . $this->title . PHP_EOL . $this->message;
    }
}
