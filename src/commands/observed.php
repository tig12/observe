<?php
/******************************************************************************
    
    Computes the observed distributions of a study.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:50:23+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands;

use observe\model\Observe;
use observe\app\ICommand;
use observe\model\IStudy;
use observe\model\distrib\Distribs;
use tiglib\time\seconds2HHMMSS;

class observed implements ICommand {
    
    /** 
        Called by Run::runCommand()
    **/
    public static function execute(IStudy $study, array $params): string {
        //
        // Parameter check
        //
        if(count($params) != 0){
            return "INVALID PARAMETER: \"{$params[0]}\". This command must be called without parameter\n";
        }
        //
        // Execute
        //
        
        $t1 = microtime(true);
        
        $filename = 'compress.bzip2://' . $study->getDatafile();
        echo "Processing $filename\n";
        //
        // function passed to computeDistributions()
        //
        $f = function() use ($filename) {
            $count = 0;
            if(!$fileHandle = fopen($filename, 'r')) {
                return false;
            }
            while(false !== $line = fgets($fileHandle)){
                yield explode(Observe::CSV_SEP, trim($line));
                $count++;
                if($count % 100000 == 0){
                    echo "$count\n";
                }
            }
            fclose($fileHandle);
        };
        $distribs = Distribs::computeDistributions($f, $study->config['dates'], $study->config['planets']);
        $outDir = $study->getObservedDirectory();
        Distribs::storeDistributions($outDir, $distribs, $study->config['dates']);
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Generated observed distributions in $outDir\n";
        echo "Execution time $dt s - $dth\n";
        return '';
    }
    
} // end class
