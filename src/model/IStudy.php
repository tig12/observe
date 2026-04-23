<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-12 00:42:19+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model;

interface IStudy {
    
    /** 
        @param  $studySlug Unique identifier of a study (entry "slug" in the yaml files in config/).
    **/
    public function __construct(string $studySlug);
    
} // end class

