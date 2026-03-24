<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-12 00:42:19+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model;

interface IStudy {
    
    /** 
        @param  $studyFile  Associative array containing the contents of a yaml study file.
    **/
    public static function init(array &$studyConfig): void;
    
    /**
        Returns the names of the directories of each subgroup of a split.
    **/
    public static function getSplitDirnames(string $split): array;
        
} // end class

