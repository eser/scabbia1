<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Views;

use Scabbia\Extensions\Views\Views;

/**
 * Views Extension: View Class
 *
 * @package Scabbia
 * @subpackage Views
 * @version 1.1.0
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
    public function setRange(array $uArray)
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
    public function renderFile()
    {
        Views::viewFile($this->path, $this->vars);
    }
}
