<?php
/******************************************************************************
    
    Generates statistics for distributions of a given split.
    
    
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

class stats implements ICommand {
    
    const array CSV_FIELDS = [
        // id of the distrib
        'DATE_NAME',
        'DISTRIB_TYPE',
        'DISTRIB',
        // statistical infos
        'MIN',
        'MAX',
        'MEAN',
        'SIGMA2',
        'CHI2',
        'P',
        'P < 0.05',
        
    ];
    
    
    /**
        Called by Studies::runCommand()
    **/
    public static function execute(array &$studyConfig, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe <study> stats <split>\n"
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
        //
        $res = implode(Observe::CSV_SEP, self::CSV_FIELDS) . "\n";
        //
        foreach($subgroupDirs as $subgroupDir){
            $observedDistribs = Distribs::loadDistributions(Studies::getObservedDirectory($studyConfig, $split, $subgroupDir), $studyConfig);
            $expectedDistribs = Distribs::loadDistributions(Studies::getExpectedDirectory($studyConfig, $split, $subgroupDir), $studyConfig);
            //
            // distributions of type distrib1
            //
            for($i=0; $i < $nDates; $i++){
                $dateName = $studyConfig['dates'][$i]; // ex: birth
                // day
                $res .= self::statsLine(
                    key1:           $dateName,
                    key2:           'day',
                    key3:           '',
                    //
// TODO compute fake values
                    min:            999,
                    max:            999,
                    mean:           999,
                    sigma:          999,
                    chi2:           999,
                    p_value:        999,
                    //
                    studyConfig:    $studyConfig,
                );
                // year
                $res .= self::statsLine(
                    key1:           $dateName,
                    key2:           'year',
                    key3:           '',
                    //
// TODO compute fake values
                    min:            999,
                    max:            999,
                    mean:           999,
                    sigma:          999,
                    chi2:           999,
                    p_value:        999,
                    //
                    studyConfig:    $studyConfig,
                );
                // aspects and planets
                foreach(['aspects', 'planets'] as $distribType){
                    foreach($observedDistribs[$dateName][$distribType] as $distribName => $observedDistribValues){ // ex: $distribName = 'SO-MO'
                        [$chi2, $p_value] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName][$distribType][$distribName]);
                        $res .= self::statsLine(
                            key1:           $dateName,
                            key2:           $distribType,
                            key3:           $distribName,
                            //
                            min:            999,
                            max:            999,
                            mean:           999,
                            sigma:          999,
                            chi2:           $chi2,
                            p_value:        $p_value,
                            //
                            studyConfig:    $studyConfig,
                        );
                    }
                }
            }
            //
            // distributions of type distrib2
            //
            for($i=0; $i < $nDates; $i++){
                for($j=$i+1; $j < $nDates; $j++){
                    $dateName = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j]; // ex: birth-death
                    // age
                    $res .= self::statsLine(
                        key1:           $dateName,
                        key2:           'age',
                        key3:           '',
                        //
// TODO compute fake values
                        min:            999,
                        max:            999,
                        mean:           999,
                        sigma:          999,
                        chi2:           999,
                        p_value:        999,
                        //
                        studyConfig:    $studyConfig,
                    );
                    // interaspects
                    foreach($observedDistribs[$dateName]['interaspects'] as $distribName => $observedDistribValues){ // ex: $distribName = 'SO-SO'
                        [$chi2, $p_value] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName]['interaspects'][$distribName]);
                        $res .= self::statsLine(
                            key1:           $dateName,
                            key2:           'interaspects',
                            key3:           $distribName,
                            //
// TODO compute fake values
                            min:            999,
                            max:            999,
                            mean:           999,
                            sigma:          999,
                            chi2:           $chi2,
                            p_value:        $p_value,
                            //
                            studyConfig:    $studyConfig,
                        );
                    }
                } // end loop on $j
            } // end loop on $i
        } // end foreach($subgroupDirs)
        
        $outfilename = $outDir . DS . $subgroupDir . DS . 'stats.csv';
        file_put_contents($outfilename, $res);
        echo "Generated $outfilename\n";
        return '';
    }

    /**
        Generates a line in stats.csv
    **/
    private static function statsLine(
        string  $key1,
        string  $key2,
        string  $key3,
        //
        float   $min,
        float   $max,
        float   $mean,
        float   $sigma,
        float   $chi2,
        float   $p_value,
        //
        array   &$studyConfig,
    ) {
        return
            $key1 . Observe::CSV_SEP
          . $key2 . Observe::CSV_SEP
          . $key3 . Observe::CSV_SEP
          //
          . $min . Observe::CSV_SEP
          . $max . Observe::CSV_SEP
          . $mean . Observe::CSV_SEP
          . $sigma . Observe::CSV_SEP
          . $chi2 . Observe::CSV_SEP
          . $p_value . Observe::CSV_SEP
          //
          . ($p_value < $studyConfig['p-value-limit'] ? 'Y' : '')
          . "\n";
    }
    
} // end class
