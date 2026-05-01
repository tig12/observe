<?php
/******************************************************************************
    
    Computes the expected distributions of a study from control groups.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-23 17:05:06+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands;

use observe\model\Observe;
use observe\app\ICommand;
use observe\model\IStudy;
use observe\model\Studies;
use observe\model\distrib\Distribs;
use observe\model\distrib\EmptyDistribs;
use observe\model\distrib\AddDistribs;
use tiglib\time\seconds2HHMMSS;

class expected implements ICommand {
    
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

        $t1 = microtime(true);
        
        //
        // Load control distribs
        //
        $controlDirs = $study->getControlSubdirectories();
        $nControls = count($controlDirs);
        $allControlDistribs = EmptyDistribs::initializeDistributions($study->config['dates'], $study->config['planets']);
        
        foreach($controlDirs as $controlDir){
            $controlDistrib = Distribs::loadDistributions($controlDir, $study->config['dates'], $study->config['planets']);
            $allControlDistribs = AddDistribs::add($allControlDistribs, $controlDistrib, $study->config['dates']);
        }
        //
        // Compute expected distribs
        //
        $nDates = count($study->config['dates']);
        $precision = $study->config['expected-precision'];
        
        $expectedDistribs = EmptyDistribs::initializeDistributions($study->config['dates'], $study->config['planets']);
        
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $study->config['dates'][$i]; // ex: birth
            // planet positions
            foreach($allControlDistribs[$dateName]['positions'] as $distribName => $controlDistribValues){ // ex: $distribName = 'SO-MO'
                foreach($controlDistribValues as $k => $v){
                    $expectedDistribs[$dateName]['positions'][$distribName][$k] = round($v / $nControls, $precision);
                }
            }
            // aspects
            foreach($allControlDistribs[$dateName]['aspects']['dim1'] as $distribName => $controlDistribValues){ // ex: $distribName = 'SO-MO'
                foreach($controlDistribValues as $k => $v){
                    $expectedDistribs[$dateName]['aspects']['dim1'][$distribName][$k] = round($v / $nControls, $precision);
                }
            }
            // day
            foreach($allControlDistribs[$dateName]['day'] as $k => $v){ // ex: $k = '01-01'
                $expectedDistribs[$dateName]['day'][$k] = round($v / $nControls, $precision);
            }
            // year
            // in theory should not computed because observed and expected values may differ
            foreach($allControlDistribs[$dateName]['year'] as $k => $v){ // ex: $k = '1935'
                $expectedDistribs[$dateName]['year'][$k] = round($v / $nControls, $precision);
            }
        }
        
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j]; // ex: birth-death
                // interaspects
                foreach($allControlDistribs[$dateName]['interaspects']['dim1'] as $distribName => $controlDistribValues){ // ex: $distribName = 'SO-SO'
                    foreach($controlDistribValues as $k => $v){ // $k: 0 ... 359
                        $expectedDistribs[$dateName]['interaspects']['dim1'][$distribName][$k] = round($v / $nControls, $precision);
                    }
                }
                // age
                // in theory should not computed because observed and expected values may differ
//                foreach(['D', 'M', 'Y'] as $unit){
                foreach(['M', 'Y'] as $unit){
                    foreach($allControlDistribs[$dateName]['age']['dim1']["age-$unit"] as $k => $v){ // $k = age
                        $expectedDistribs[$dateName]['age']['dim1']["age-$unit"][$k] = round($v / $nControls, $precision);
                    }
                }
            } // end loop on $j
        } // end loop on $i
        //
        // Store results
        //
        $outDir = $study->getExpectedDirectory();
        Distribs::storeDistributions($outDir, $expectedDistribs, $study->config['dates']);
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Stored expected distributions in $outDir\n";
        echo "Execution time $dt s - $dth\n";
        return '';
    }
    
} // end class
