<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mvc;

use Scabbia\Extensions\Io;
use Scabbia\Extensions\Mvc\ControllerBase;
use Scabbia\Utils;

/**
 * Mvc Extension: Controller Class
 *
 * @package Scabbia
 * @subpackage Mvc
 * @version 1.1.0
 */
abstract class Controller extends ControllerBase
{
    /**
     * @ignore
     */
    public function mapDirectory($uDirectory, $uExtension, $uAction, $uArgs)
    {
        $tMap = Io::mapFlatten(Utils::translatePath($uDirectory), '*' . $uExtension, true, true);

        array_unshift($uArgs, $uAction);
        $tPath = implode('/', $uArgs);

        if (in_array($tPath, $tMap, true)) {
            $this->view($uDirectory . $tPath . $uExtension);

            return true;
        }

        return false;
    }
}
