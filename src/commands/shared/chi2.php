<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:49:38+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use observe\model\ICommand;
use observe\model\Observe;
use observe\model\Studies;
use observe\model\distrib\Distribs;
use tiglib\stats\chi2 as chi2compute;
use tiglib\time\seconds2HHMMSS;

class chi2 implements ICommand {
    
    /**
        Called by Studies::runCommand()
    **/
    public static function execute(array &$studyConfig, array $params): string {
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
        // Execute
        //
        $outDir = Studies::getSplitDirectory($studyConfig, $split);
        $nDates = count($studyConfig['dates']);
        $precision = $studyConfig['expected-precision'];
        $subgroupDirs = Studies::getStudyClasspath($studyConfig['slug'])::getSplitDirnames($split);
//        $chi2s =[];
$csvChi2 = "DATE_NAME;DISTRIB_TYPE;DISTRIB;CHI2;P;P < 0.05\n";
        foreach($subgroupDirs as $subgroupDir){
            $observedDistribs = Distribs::loadDistributions(Studies::getObservedDirectory($studyConfig, $split, $subgroupDir), $studyConfig);
            $expectedDistribs = Distribs::loadDistributions(Studies::getExpectedDirectory($studyConfig, $split, $subgroupDir), $studyConfig);
            // distributions of type distrib1
            for($i=0; $i < $nDates; $i++){
                $dateName = $studyConfig['dates'][$i]; // ex: birth
                // aspects and planets
                foreach(['aspects', 'planets'] as $distribType){
                    foreach($observedDistribs[$dateName][$distribType] as $distribName => $observedDistribValues){ // ex: $distribName = 'SO-MO'
// echo "[$dateName][$distribType][$distribName]\n";
                        //$chi2s[$dateName][$distribType][$distribName] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName][$distribType][$distribName]);
//                        $chi2s["$dateName/$distribType/$distribName"] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName][$distribType][$distribName]);
$csvChi2 .= self::chi2line($dateName, $distribType, $distribName, chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName][$distribType][$distribName]));
                    }
                }
                // day
// echo "[$dateName]['day']\n";
//                $chi2s[$dateName]['day'] = chi2compute::chi2AndProba(359, $observedDistribs[$dateName]['day'], $expectedDistribs[$dateName]['day']);
                // year
// echo "[$dateName]['year']\n";
//                $chi2s[$dateName]['year'] = chi2compute::chi2AndProba(359, $observedDistribs[$dateName]['year'], $expectedDistribs[$dateName]['year']);
            }
            // distributions of type distrib1
            for($i=0; $i < $nDates; $i++){
                for($j=$i+1; $j < $nDates; $j++){
                    $dateName = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j]; // ex: birth-death
                    // interaspects
                    foreach($observedDistribs[$dateName]['interaspects'] as $distribName => $observedDistribValues){ // ex: $distribName = 'SO-SO'
// echo "[$dateName]['interaspects'][$distribName]\n";
                        //$chi2s[$dateName]['interaspects'][$distribName] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName]['interaspects'][$distribName]);
//                        $chi2s["$dateName/interaspects/$distribName"] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName]['interaspects'][$distribName]);
$csvChi2 .= self::chi2line($dateName, 'interaspects', $distribName, chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName]['interaspects'][$distribName]));
                    }
                    // age
// echo "[$dateName]['age']\n";
//                    $chi2s[$dateName]['age'] = chi2compute::chi2AndProba(359, $observedDistribs[$dateName]['age'], $expectedDistribs[$dateName]['age']);
                } // end loop on $j
            } // end loop on $i
        } // end foreach($subgroupDirs)
        $outfilename = $outDir . DS . $subgroupDir . DS . 'chi2.csv';
        file_put_contents($outfilename, $csvChi2);
echo "Generated $outfilename\n";
//echo "\n"; print_r($chi2s); echo "\n";
//echo "$csvChi2\n";
        return '';
    }

    
    /**
        @param  $
    **/
    private static function chi2line(string $k1, string $k2, string $k3, array $chi2AndProba) {
        return
            $k1 . Observe::CSV_SEP
          . $k2 . Observe::CSV_SEP
          . $k3 . Observe::CSV_SEP
          . $chi2AndProba[0] . Observe::CSV_SEP
          . $chi2AndProba[1] . Observe::CSV_SEP
          . ($chi2AndProba[1] < 0.05 ? 'Y' : '')
          . "\n";
    }
} // end class
