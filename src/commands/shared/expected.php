<?php
/******************************************************************************
    
    Computes the expected distributions of a given split from control groups.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-23 17:05:06+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use observe\model\Observe;
use observe\model\ICommand;
use observe\model\Studies;
use observe\model\distrib\Distribs;
use observe\model\distrib\EmptyDistribs;
use observe\model\distrib\AddDistribs;

class expected implements ICommand {
    
    /** 
        Called by Studies::runCommand()
    **/
    public static function execute(array $studyConfig, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe <study> expected <split>\n"
            . "<split> can be:\n  - " . implode("\n  - ", $studyConfig['splits']) . "\n";
        if(count($params) != 1){
            return "MISSING PARAMETER split.\n$usage";
        }
        $split = $params[0];
        if(!in_array($split, $studyConfig['splits'])){
            return "INVALID PARAMETER split: \"$split\".\n$usage";
        }
        //
        // Load control distribs
        //
        $baseControlsDir = Studies::getControlsDirectory($studyConfig);
        $controlDirs = glob($baseControlsDir . DS . 'control-*');
        $nControls = count($controlDirs);
        $allControlDistribs = EmptyDistribs::initializeDistributions($studyConfig);
        foreach($controlDirs as $controlDir){
            $controlDistrib = Distribs::loadDistributions($controlDir, $studyConfig);
            $allControlDistribs = AddDistribs::add($allControlDistribs, $controlDistrib, $studyConfig);
        }
        //
        // Compute expected distribs
        //
        $classpath = Studies::getStudyClasspath($studyConfig['slug']);
        $subgroupDirs = $classpath::getSplitDirnames($split);
print_r($subgroupDirs);
exit;
        $expectedDistribs = EmptyDistribs::initializeDistributions($studyConfig);
        $precision = 2;
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $studyConfig['dates'][$i]; // ex: birth
            // aspects and planets
            foreach(['aspects', 'planets'] as $distribType){
                foreach($allControlDistribs[$dateName][$distribType] as $distribName => $controlDistribValues){
                    foreach($controlDistribValues as $k => $v){
                        $expectedDistribs[$dateName][$distribType][$distribName][$k] = round($v / $nControls, $precision);
                    }
                }
            }
            // day
            foreach($allControlDistribs[$dateName]['day'] as $k => $v){
                $expectedDistribs[$dateName]['day'][$k] = round($v / $nControls, $precision);
            }
            // year
            foreach($d2[$dateName]['year'] as $k => $v){
                $expectedDistribs[$dateName]['year'][$k] = round($v / $nControls, $precision);
            }
        }
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j]; // ex: birth-death
                // interaspects
                foreach($allControlDistribs[$dateName]['interaspects'] as $distribName => $controlDistribValues){
                    foreach($d2[$dateName]['interaspects'][$distribName] as $k => $v){
                        $expectedDistribs[$dateName]['interaspects'][$distribName][$k] = round($v / $nControls, $precision);
                    }
                }
                // age
                $expectedDistribs[$dateName]['age'] = $allControlDistribs[$dateName]['age'];
                foreach($d2[$dateName]['age'] as $k => $v){
                    $expectedDistribs[$dateName]['age'][$k] = round($v / $nControls, $precision);
                }
            } // end loop on $j
        } // end loop on $i
        //
        // Store results
        //
        $outDir = Studies::getExpectedDirectory($studyConfig, $split);
        Distribs::storeDistributions($outDir, $distribs, $studyConfig);
        return '';
    }
    
} // end class
