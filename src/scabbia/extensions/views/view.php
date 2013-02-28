<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Views\Views;

/**
 * View Class
 *
 * @package Scabbia
 * @subpackage LayerExtensions
 */
class View
{
    /**
     * @ignore
     */
    public $path;
    /**
     * @ignore
     */
    public $vars;


    /**
     * @ignore
     */
    public function __construct($uPath)
    {
        $this->path = $uPath;
    }

    /**
     * @ignore
     */
    public function get($uKey)
    {
        return $this->vars[$uKey];
    }

    /**
     * @ignore
     */
    public function set($uKey, $uValue)
    {
        $this->vars[$uKey] = $uValue;
    }

    /**
     * @ignore
     */
    public function setRef($uKey, &$uValue)
    {
        $this->vars[$uKey] = $uValue;
    }

    /**
     * @ignore
     */
    public function setRange($uArray)
    {
        foreach ($uArray as $tKey => $tValue) {
            $this->vars[$tKey] = $tValue;
        }
    }

    /**
     * @ignore
     */
    public function remove($uKey)
    {
        unset($this->vars[$uKey]);
    }

    /**
     * @ignore
     */
    public function render()
    {
        Views::view($this->path, $this->vars);
    }

    /**
     * @ignore
     */
    public function renderFile()
    {
        Views::viewFile($this->path, $this->vars);
    }
}
