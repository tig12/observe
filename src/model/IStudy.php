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
    
    public function init($params = []): string;
    public function import($params = []): string;
    public function observed($params = []): string;
    public function control($params = []): string;
    public function expected($params = []): string;
    public function stats($params = []): string;
    public function dim2($params = []): string;
    public function output($params = []): string;
    public function dev($params = []): string;
    
} // end class

