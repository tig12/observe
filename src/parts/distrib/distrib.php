<?php
/******************************************************************************
    Common code to several distributions.
    
    @license    GPL
    @history    2021-03-14 17:59:59+01:00, Thierry Graff : Creation from existing code
********************************************************************************/
namespace observe\parts\distrib;

use observe\app\Observe;

class distrib {
    
    // ******************************************************
    /**
        Builds the content of a csv file containing a distribution
        A distribution is just an associative array $k => $v
    **/
    public static function distrib2csv(&$distrib): string {
        $res = '';
        foreach($distrib as $k => $v){
            $res .= $k . Observe::CSV_SEP . $v . "\n";
        }
        return $res;
    }
    
    /** 
        Returns true if a line of a distribution should be skipped.
    **/
    public static function skipLine(&$line, &$key, &$skip): bool {
        return $line[$key] == $skip;
    }
    
} // end class
