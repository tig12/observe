<?php
/******************************************************************************
    A split is a subgroup of a dataset.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-19 17:44:18+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model;

class Splits {
    
    /**
        Computes the directory containing the intermediate files of a split.
    **/
    public static function getSplitDirectory(array &$studyConfig, string $split): string {
        return $studyConfig['working-dir'] . DS . 'split-' . $split;
    }
    
} // end class


