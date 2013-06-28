<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

use Scabbia\Delegate;

/**
 * Default environment.
 *
 * @package Scabbia
 * @version 1.1.0
 */
class Environment
{
    /**
     * @var string      environment name
     */
    public $name;


    /**
     * Default entry point and definitions for an environment.
     *
     * @param string $uName      environment name
     */
    public function __construct($uName)
    {
        $this->name = $uName;
    }
}
