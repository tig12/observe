<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:49:38+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use observe\model\ICommand;
use observe\model\Studies;
use tiglib\stats\chi2 as chi2compute;
use observe\model\distrib\Distribs;
use tiglib\time\seconds2HHMMSS;

class chi2 implements ICommand {
    
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
    
        $nDates = count($studyConfig['dates']);
        $precision = $studyConfig['expected-precision'];
        $subgroupDirs = Studies::getStudyClasspath($studyConfig['slug'])::getSplitDirnames($split);
        $chi2s =[];
        foreach($subgroupDirs as $subgroupDir){
            $observedDistribs = Distribs::loadDistributions(Studies::getObservedDirectory($studyConfig, $split, $subgroupDir), $studyConfig);
            $expectedDistribs = Distribs::loadDistributions(Studies::getExpectedDirectory($studyConfig, $split, $subgroupDir), $studyConfig);
            // distributions of type distrib1
            for($i=0; $i < $nDates; $i++){
                $dateName = $studyConfig['dates'][$i]; // ex: birth
                // aspects and planets
                foreach(['aspects', 'planets'] as $distribType){
                    foreach($observedDistribs[$dateName][$distribType] as $distribName => $observedDistribValues){ // ex: $distribName = 'SO-MO'
                        $chi2s[$dateName][$distribType][$distribName] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName][$distribType][$distribName]);
                    }
                }
                // day
                $chi2s[$dateName]['day'] = chi2compute::chi2AndProba(359, $observedDistribs[$dateName]['day'], $expectedDistribs[$dateName]['day']);
                // year
                $chi2s[$dateName]['year'] = chi2compute::chi2AndProba(359, $observedDistribs[$dateName]['year'], $expectedDistribs[$dateName]['year']);
            }
            // distributions of type distrib1
            for($i=0; $i < $nDates; $i++){
                for($j=$i+1; $j < $nDates; $j++){
                    $dateName = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j]; // ex: birth-death
                    // interaspects
                    foreach($observedDistribs[$dateName]['interaspects'] as $distribName => $observedDistribValues){ // ex: $distribName = 'SO-SO'
                        $chi2s[$dateName]['interaspects'][$distribName] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName][$distribType][$distribName]);
                    }
                    // age
                    $chi2s[$dateName]['age'] = chi2compute::chi2AndProba(359, $observedDistribs[$dateName]['age'], $expectedDistribs[$dateName]['age']);
                } // end loop on $j
            } // end loop on $i
        }
echo "\n"; print_r($chi2s); echo "\n";
        return '';
    }
    
} // end class
