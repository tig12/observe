<?php
/******************************************************************************
    Specific to 
    
    @license    GPL
    @history    2021-05-12 04:30:38+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\custom\commands;

use observe\Observe;
use observe\patterns\Command;
use observe\ObserveException;

class gq20916 implements Command {
    
    public static function execute($params=[]){
        //
        // check parameters
        //
        $classname = __CLASS__;
        if(!isset($params['commands'])){
            throw new ObserveException("$classname needs a parameter 'commands'");
        }
echo "NOT IMPLEMENTED: $classname\n";
return;
        // TODO
        // Here need to access to the other commands of the command file
        foreach($params['commands'] as $cmdStr){
        }
    }
    
}// end class
