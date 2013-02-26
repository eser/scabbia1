<?php

namespace Scabbia\Extensions\Oauth;

use Scabbia\Extensions\Mvc\Controller;

/**
 * Docs Extension
 *
 * @package Scabbia
 * @subpackage oauth
 * @version 1.1.0
 *
 * @scabbia-fwversion 1.1
 * @scabbia-fwdepends
 * @scabbia-phpversion 5.3.0
 * @scabbia-phpdepends
 */
class Oauth extends Controller
{
    /**
     * @ignore
     */
    public function index()
    {
        $this->view('{vendor}views/oauth/index.php');
    }
}
