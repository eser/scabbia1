<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Oauth;

use Scabbia\Extensions\Mvc\Controller;

/**
 * Oauth Extension
 *
 * @package Scabbia
 * @subpackage Oauth
 * @version 1.1.0
 */
class Oauth extends Controller
{
    /**
     * @ignore
     */
    public function index()
    {
        $this->view('{core}views/oauth/index.php');
    }
}
