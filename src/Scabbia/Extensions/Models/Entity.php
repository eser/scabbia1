<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Models;

/**
 * Models Extension: Entity Class
 *
 * @package Scabbia
 * @subpackage Models
 * @version 1.1.0
 *
 * @todo entity set for group of entities, maybe derived from Collection?
 */
class Entity
{
    /**
     * @ignore
     */
    public static function getFromRequest()
    {
        return new static($_POST);
    }

    /**
     * @ignore
     */
    public function __construct(array $uFields = null)
    {
        $tFields = get_class_vars(get_class($this));

        if (isset($uFields)) {
            foreach ($tFields as $tField) {
                if (!isset($uFields[$tField])) {
                    continue;
                }

                $this->{$tField} = $uFields[$tField];
            }
        }
    }

    /**
     * @ignore
     */
    public function validate()
    {
        return true;
    }
}
