<?php
/******************************************************************************
    
    Checks if expected and observed distributions have the same total
    This command supposes that the hierarchy of files in var/studies/death-fr conforms with the other commands
    obs = observed
    exp = expected
    
    @license    GPL
    @history    2026-02-27 15:19:40+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use observe\app\Command;
use observe\shared\distrib\csvDistrib;

class check implements Command {
    
    public static function execute($params=[]){
        
        $sums = [];
        // directory containing expected frequencies
        $dir_exp = DeathFr::$WORKING_DIR . DS . 'expected';
        $files = glob($dir_exp . DS . '*.csv');
        foreach($files as $file){
            $distrib = csvDistrib::csv2distrib($file, false);
            $key = substr(basename($file), 0, 5);
            $sums["expected-$key"] = array_sum($distrib);
        }
        // directories containing the observed frequencies, for all splits
        foreach(DeathFr::$POSSIBLE_SPLITS as $split){
//echo "split = $split\n";
            $split_dir = DeathFr::$WORKING_DIR . DS . 'split-' . $split . DS . 'distrib-aspects';
            $split_subdirs = glob($split_dir . DS . '*');
            foreach($split_subdirs as $split_subdir){
//echo $split_subdir . "\n";
//continue;
                // each subdir contains te distributions
                $files = glob($split_subdir . DS . '*.csv');
                foreach($files as $file){
                    $distrib = csvDistrib::csv2distrib($file, false);
                    $key = $split . '-' . basename($split_subdir) . '-' . substr(basename($file), 0, 5);
                    $sums[$key] = array_sum($distrib);
                }
            }
        }
echo "\n"; print_r($sums); echo "\n";
    }
    
} // end class
