<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia;

/**
 * Default application.
 *
 * @package Scabbia
 * @version 1.1.0
 */
class Application
{
    /**
     * @var string      application name
     */
    public $name = 'Default';
    /**
     * @var string      application directory
     */
    public $directory = 'application/';
    /**
     * @var array       callback definitions
     */
    public $callbacks = array();
    /**
     * @var null|string if any of callbacks does not fit
     */
    public $otherwise = null;
    /**
     * @var null|string if any error occurred during process
     */
    public $onError = null;


    /**
     * Default entry point and definitions for an application.
     */
    public function __construct()
    {
        $this->callbacks[] = 'Scabbia\\Extensions\\Http\\Http::routing';
        $this->callbacks[] = 'Scabbia\\Extensions\\Assets\\Assets::routing';

        $this->otherwise = 'Scabbia\\Extensions\\Http\\Http::notfound';
        $this->onError = 'Scabbia\\Extensions\\Http\\Http::error';
    }
}
