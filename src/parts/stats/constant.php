<?php
/******************************************************************************
        
    @license    GPL
    @history    2021-03-07 18:30:03+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\stats;

use observe\app\Observe;

class constant {
    
    /** 
        Loads a constant from a txt file.
        The content of the file is intepreted as a scalar value
    **/
    public static function loadFromTXT(string $filename) {
        return trim(file_get_contents($filename));
    }
    
    
} // end class
