<?php
/******************************************************************************
    
    Builds average distributions from control groups.
    
    @license    GPL
    @history    2026-02-25 21:50:52+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use tiglib\patterns\command\Command;

class expected implements Command {
    
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
        if(!isset($params['in-subdir'])){
            echo "Missing parameter 'in-subdir' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        $inDir = DeathFr::$WORKING_DIR . DS . $params['in-subdir'];
        if(!is_dir($inDir)){
            // Not created to avoid mistakes
            echo "Directory $inDir does not exist. Create it before executing this command\n";
            return;
        }
        //
        // Execute
        //
        $avgDistrib = degreeUtils::emptyDoubleDistrib($allPlanets, $allPlanets);
        // planet codes
        $allPlanets = DeathFr::$PLANETS;
        
        $controlDirs = glob($inDir. DS . '*');
        $nControls = count($controlDirs);
        foreach($controlDirs as $controlDir){
            $csvFiles = glob($controlDir . DS . '*.csv');
            foreach($csvFiles as $csvFile){
                // parse the file name to know the key of the distribution
                
            }
        }
echo "\n"; print_r($controlDirs); echo "\n";
    }
    
} // end class
