<?php
/******************************************************************************
    
    Computes the observed distributions for a given split.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:50:23+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use observe\model\Observe;
use observe\model\ICommand;
use observe\model\Studies;
use observe\model\distrib\Distribs;

class observed implements ICommand {
    
    /** 
        Called by Studies::runCommand()
    **/
    public static function execute(array $studyConfig, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe <study> observed <split>\n"
            . "<split> can be:\n  - " . implode("\n  - ", $studyConfig['splits']) . "\n";
        if(count($params) != 1){
            return "MISSING PARAMETER split.\n$usage";
        }
        $split = $params[0];
        if(!in_array($split, $studyConfig['splits'])){
            return "INVALID PARAMETER split: \"$split\".\n$usage";
        }
        //
        // Execute
        //
        $baseOutdir = Studies::getSplitDirectory($studyConfig, $split);
        $dirs = glob($baseOutdir . DS . '*');
        foreach($dirs as $dir){
            $filename = 'compress.bzip2://' . $dir . DS . 'data.csv.bz2';
            echo "=== Processing $filename ===\n";
            //
            // function passed to computeDistributions()
            //
            $f = function() use ($filename) {
                if (!$fileHandle = fopen($filename, 'r')) {
                    return false;
                }
                while(false !== $line = fgets($fileHandle)){
                    yield explode(Observe::CSV_SEP, trim($line));
                }
                fclose($fileHandle);
            };
            $distribs = Distribs::computeDistributions($f, $studyConfig);
            $outDir = $dir . DS . 'observed';
            Distribs::storeDistributions($outDir, $distribs, $studyConfig);
        }
        return '';
    }
    
} // end class
