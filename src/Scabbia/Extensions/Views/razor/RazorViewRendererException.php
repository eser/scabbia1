<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Views\Razor;

/**
 * Views Extension: RazorViewRendererException Class
 * RazorViewRendererException represents a generic exception for razor view render extension.
 *
 * @package Scabbia
 * @subpackage Views
 * @version 1.1.0
 *
 * @author Stepan Kravchenko <stepan.krab@gmail.com>
 */
class RazorViewRendererException extends \Exception
{
    /**
     * @ignore
     */
    public function __construct($message, $templateFileName, $line)
    {
        parent::__construct("Invalid view template: {$templateFileName}, at line {$line}. {$message}", null, null);
    }
}
