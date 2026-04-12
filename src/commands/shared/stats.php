<?php
/******************************************************************************
    
    Generates statistics for distributions of a given split, stored in stats.csv
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:49:38+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use observe\model\ICommand;
use observe\model\Observe;
use observe\model\Studies;
use observe\model\distrib\Distribs;
use observe\model\distrib\StatsDistrib;
use tiglib\stats\chi2 as chi2compute;
use tiglib\stats\minMax;
use tiglib\stats\distrib as distribIndicators;

class stats implements ICommand {
    
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
        //$subgroupDirs = Studies::getStudyClasspath($studyConfig['slug'])::getSplitSubgroups($split);
        $subgroupDirs = Studies::getSplitSubgroups($studyConfig, $split);
        //
        $res = implode(Observe::CSV_SEP, StatsDistrib::STATS_CSV_FIELDS) . "\n";
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
                [$chi2, $p_value] = chi2compute::chi2AndProba(359, $observedDistribs[$dateName]['day'], $expectedDistribs[$dateName]['day']);
                $res .= self::statsLine(
                    studyConfig:    $studyConfig,
                    distrib:        $observedDistribs[$dateName]['day'],
                    key1:           $dateName,
                    key2:           'day',
                    key3:           '',
                    chi2:           $chi2,
                    p_value:        $p_value,
                );
                // year
                //[$chi2, $p_value] = chi2compute::chi2AndProba(359, $observedDistribs[$dateName]['year'], $expectedDistribs[$dateName]['year']);
                $res .= self::statsLine(
                    studyConfig:    $studyConfig,
                    distrib:        $observedDistribs[$dateName]['year'],
                    key1:           $dateName,
                    key2:           'year',
                    key3:           '',
                    //chi2:           $chi2,
                    //p_value:        $p_value,
                );
                // aspects and planets
                foreach(['aspects', 'planets'] as $distribType){
                    foreach($observedDistribs[$dateName][$distribType] as $distribName => $observedDistribValues){ // ex: $distribName = 'SO-MO'
                        [$chi2, $p_value] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName][$distribType][$distribName]);
                        $res .= self::statsLine(
                            studyConfig:    $studyConfig,
                            distrib:        $observedDistribValues,
                            key1:           $dateName,
                            key2:           $distribType,
                            key3:           $distribName,
                            chi2:           $chi2,
                            p_value:        $p_value,
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
                    [$chi2, $p_value] = chi2compute::chi2AndProba(359, $observedDistribs[$dateName]['age'], $expectedDistribs[$dateName]['age']);
                    $res .= self::statsLine(
                        studyConfig:    $studyConfig,
                        distrib:        $observedDistribs[$dateName]['age'],
                        key1:           $dateName,
                        key2:           'age',
                        key3:           '',
                        chi2:           $chi2,
                        p_value:        $p_value,
                    );
                    // interaspects
                    foreach($observedDistribs[$dateName]['interaspects'] as $distribName => $observedDistribValues){ // ex: $distribName = 'SO-SO'
                        [$chi2, $p_value] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName]['interaspects'][$distribName]);
                        $res .= self::statsLine(
                            studyConfig:    $studyConfig,
                            distrib:        $observedDistribValues,
                            key1:           $dateName,
                            key2:           'interaspects',
                            key3:           $distribName,
                            chi2:           $chi2,
                            p_value:        $p_value,
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
        Generates a line for a distribution in stats.csv
        The fields and their order are defined in StatsDistrib::STATS_CSV_FIELDS
            // id of the distrib
            DATE_NAME
            DISTRIB_TYPE
            DISTRIB
            // statistical infos
            FROM
            TO
            MIN_KEY
            MIN
            MAX_KEY
            MAX
            MEAN
            SIGMA
            CHI2
            P
            P<LIMIT
    **/
    private static function statsLine(
        array   &$studyConfig,
        array   &$distrib,
        string  $key1,
        string  $key2,
        string  $key3,
        ?float  $chi2 = null,
        ?float  $p_value = null,
    ) {
        $min = min($distrib);
        $max = max($distrib);
        [$from, $to, $min_key, $min, $max_key, $max] = minMax::minMaxIndicators($distrib);
        //
        $mean = distribIndicators::mean($distrib);
        $mean = round($mean, 2);                        // WARNING round() is done here - pass in parameter ?
        $sigma = distribIndicators::sigma($distrib);
        //
        if(is_null($chi2)){
            $chi2 = '';
            $p_value = '';
            $p_inf_limit = '';
        }
        else{
            $chi2 = round($chi2, 3);                    // WARNING round() is done here - pass in parameter ?
            //$p_value = round($p_value, 5);              // WARNING round() is done here - pass in parameter ?
            $p_inf_limit = ($p_value < $studyConfig['p-value-limit'] ? 'Y' : '');
        }
        //
        return
            $key1                   . Observe::CSV_SEP
          . $key2                   . Observe::CSV_SEP
          . $key3                   . Observe::CSV_SEP
          //
          . $from                   . Observe::CSV_SEP
          . $to                     . Observe::CSV_SEP
          . $min_key                . Observe::CSV_SEP
          . $min                    . Observe::CSV_SEP
          . $max_key                . Observe::CSV_SEP
          . $max                    . Observe::CSV_SEP
          . $mean                   . Observe::CSV_SEP
          . $sigma                  . Observe::CSV_SEP
          . $chi2                   . Observe::CSV_SEP
          . $p_value                . Observe::CSV_SEP
          . $p_inf_limit
          . "\n";
    }
    
} // end class
