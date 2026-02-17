<?php
/******************************************************************************
    
    @license    GPL
    @history    2026-02-17 00:44:29+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\studies\deathfr;

use observe\app\Command;

class aspects implements Command {
    
    public static function execute($params=[]){
die("\n<br>die here " . __FILE__ . ' - line ' . __LINE__ . "\n");
    }
    
} // end class
