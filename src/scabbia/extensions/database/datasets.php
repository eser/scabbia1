<?php
/**
 * Scabbia Framework Version 1.1
 * https://github.com/larukedi/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Database;

use Scabbia\Extensions\Database\DatabaseDataset;
use Scabbia\Config;

/**
 * Datasets Class
 *
 * @package Scabbia
 * @subpackage LayerExtensions
 */
class Datasets
{
    /**
     * @ignore
     */
    public static $datasets = null;


    /**
     * @ignore
     */
    public static function get($uDataset = null)
    {
        if (is_null(self::$datasets)) {
            foreach (Config::get('datasetList', array()) as $tDatasetConfig) {
                $tDataset = new DatabaseDataset($tDatasetConfig);
                self::$datasets[$tDataset->id] = $tDataset;
            }
        }

        return self::$datasets[$uDataset];
    }
}
