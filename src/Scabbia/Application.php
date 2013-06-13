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
 *
 * @todo generate('GET', 'test/test');
 */
class Application
{
    /**
     * @var string      application name
     */
    public $name;
    /**
     * @var string      application directory
     */
    public $directory;
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
     *
     * @param string $uName      application name
     * @param string $uDirectory application directory
     */
    public function __construct($uName = null, $uDirectory = null)
    {
        $this->name = !is_null($uName) ? $uName : 'Application';
        $this->directory = !is_null($uDirectory) ? $uDirectory : 'application/';

        $this->callbacks[] = 'Scabbia\\Extensions\\Http\\Http::routing';
        $this->callbacks[] = 'Scabbia\\Extensions\\Assets\\Assets::routing';

        $this->otherwise = 'Scabbia\\Extensions\\Http\\Http::notfound';
        $this->onError = 'Scabbia\\Extensions\\Http\\Http::error';
    }
}
