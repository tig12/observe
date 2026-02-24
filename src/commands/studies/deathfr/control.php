<?php
/******************************************************************************
    
    Builds distributions of control groups
    
    @license    GPL
    @history    2026-02-24 14:25:00+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use observe\app\Command;

class control implements Command {
        
    public static function execute($params=[]){
        //
        // Parameter check
        //
        if(!isset($params['out-subdir'])){
            echo "Missing parameter 'out-subdir' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        $outDir = DeathFr::$WORKING_DIR . DS . $params['out-subdir'];
        if(!is_dir($outDir)){
            // Not created to avoid mistakes
            echo "Directory $outDir does not exist. Create it before executing this command\n";
            return;
        }
        //
        if(!isset($params['n-controls'])){
            echo "Missing parameter 'n-controls' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        } else if(!is_int($params['n-controls'])){
            echo "Parameter 'n-controls' must be an integer in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        //
        if(!isset($params['n-start'])){
            echo "Missing parameter 'n-start' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        } else if(!is_int($params['n-start'])){
            echo "Parameter 'n-start' must be an integer in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        //
        // Execute
        //
        for($i=$params['n-start']; $i < $params['n-controls'] + $params['n-start']; $i++){
            $controlDir = str_pad($i, 3, '0', STR_PAD_LEFT);
            echo "$controlDir\n";
        }
        $t1 = microtime(true);
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        echo "(execution time $dt s)\n";
        
    }
    
} // end class
                                                                                                                               