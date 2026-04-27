<?php
/******************************************************************************
    
    Generates statistics for distributions of a study, stored in stats.csv
    Stores one stats.csv in observed (with chi2 etc.) and one stats.csv in expected (without chi2 etc.).
    
    @pre    Observed and expected distributions must have been computed.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:49:38+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands;

use observe\app\ICommand;
use observe\model\IStudy;
use observe\model\Observe;
use observe\model\Studies;
use observe\model\distrib\Distribs;
use observe\model\distrib\StatsDistrib;
use tiglib\stats\chi2 as chi2compute;
use tiglib\stats\minMax;
use tiglib\stats\distrib as distribIndicators;
use tiglib\filesystem\file_put_contents;

class stats implements ICommand {
    
    /**
        Called by Commands::runCommand)
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
        $nDates = count($study->config['dates']);
        $precision = $study->config['expected-precision'];
        
        //
        $res_obs = implode(Observe::CSV_SEP, StatsDistrib::STATS_CSV_FIELDS) . "\n";
        $res_exp = implode(Observe::CSV_SEP, StatsDistrib::STATS_CSV_FIELDS) . "\n";
        //
        $observedDistribs = Distribs::loadDistributions($study->getObservedDirectory(), $study);
        $expectedDistribs = Distribs::loadDistributions($study->getExpectedDirectory(), $study);
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = $study->config['dates'][$i]; // ex: birth
            // day
            [$chi2, $p_value] = chi2compute::chi2AndProba(359, $observedDistribs[$dateName]['day'], $expectedDistribs[$dateName]['day']);
            $res_obs .= self::statsLine(
                study:          $study,
                distrib:        $observedDistribs[$dateName]['day'],
                key1:           $dateName,
                key2:           'day',
                key3:           '',
                chi2:           $chi2,
                p_value:        $p_value,
            );
            $res_exp .= self::statsLine(
                study:          $study,
                distrib:        $expectedDistribs[$dateName]['day'],
                key1:           $dateName,
                key2:           'day',
                key3:           '',
            );
            // year - no chi2 because observed and expected distribs can be of different size (meaningless and could bug)
            $res_obs .= self::statsLine(
                study:          $study,
                distrib:        $observedDistribs[$dateName]['year'],
                key1:           $dateName,
                key2:           'year',
                key3:           '',
            );
            $res_exp .= self::statsLine(
                study:          $study,
                distrib:        $expectedDistribs[$dateName]['year'],
                key1:           $dateName,
                key2:           'year',
                key3:           '',
            );
            // aspects and planets
            foreach(['aspects', 'planets'] as $distribType){
                foreach($observedDistribs[$dateName][$distribType] as $distribName => $observedDistribValues){ // ex: $distribName = 'SO-MO'
                    [$chi2, $p_value] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName][$distribType][$distribName]);
                    $res_obs .= self::statsLine(
                        study:          $study,
                        distrib:        $observedDistribs[$dateName][$distribType][$distribName],
                        key1:           $dateName,
                        key2:           $distribType,
                        key3:           $distribName,
                        chi2:           $chi2,
                        p_value:        $p_value,
                    );
                    $res_exp .= self::statsLine(
                        study:          $study,
                        distrib:        $expectedDistribs[$dateName][$distribType][$distribName],
                        key1:           $dateName,
                        key2:           $distribType,
                        key3:           $distribName,
                    );
                }
            }
        }
        //
        // distributions of type distrib2
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j]; // ex: birth-death
                // age
                // no chi2 because observed and expected distribs can be of different size (meaningless and could bug)
                $res_obs .= self::statsLine(
                    study:          $study,
                    distrib:        $observedDistribs[$dateName]['age'],
                    key1:           $dateName,
                    key2:           'age',
                    key3:           '',
                );
                $res_exp .= self::statsLine(
                    study:          $study,
                    distrib:        $expectedDistribs[$dateName]['age'],
                    key1:           $dateName,
                    key2:           'age',
                    key3:           '',
                );
                // interaspects
                foreach($observedDistribs[$dateName]['interaspects'] as $distribName => $observedDistribValues){ // ex: $distribName = 'SO-SO'
                    [$chi2, $p_value] = chi2compute::chi2AndProba(359, $observedDistribValues, $expectedDistribs[$dateName]['interaspects'][$distribName]);
                    $res_obs .= self::statsLine(
                        study:          $study,
                        distrib:        $observedDistribs[$dateName]['interaspects'][$distribName],
                        key1:           $dateName,
                        key2:           'interaspects',
                        key3:           $distribName,
                        chi2:           $chi2,
                        p_value:        $p_value,
                    );
                    $res_exp .= self::statsLine(
                        study:          $study,
                        distrib:        $expectedDistribs[$dateName]['interaspects'][$distribName],
                        key1:           $dateName,
                        key2:           'interaspects',
                        key3:           $distribName,
                    );
                }
            } // end loop on $j
        } // end loop on $i
        
        $outFile_obs = $study->getObservedDirectory() . DS . 'stats.csv';
        $outFile_exp = $study->getExpectedDirectory() . DS . 'stats.csv';
        file_put_contents::execute($outFile_obs, $res_obs);
        file_put_contents::execute($outFile_exp, $res_exp);
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
            P_LIMIT
    **/
    private static function statsLine(
        IStudy  $study,
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
        $sigma = round($sigma, 2);                      // WARNING round() is done here - pass in parameter ?
        //
        if(is_null($chi2)){
            $chi2 = '';
            $p_value = '';
            $p_inf_limit = '';
        }
        else{
            $chi2 = round($chi2, 3);                    // WARNING round() is done here - pass in parameter ?
            $p_inf_limit = ($p_value < $study->config['p-value-limit'] ? 'Y' : '');
            if($p_value != 0 && $p_value < 0.001){
                $p_value = sprintf("%.2e", $p_value);
            }
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
