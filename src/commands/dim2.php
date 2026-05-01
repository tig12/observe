<?php
/******************************************************************************
    
    Builds dim2 observed distributions, as described in Didier Castille article
    http://cura.free.fr/xx/18cas3en.html (English) and http://cura.free.fr/xx/18cas3fr.html (French)
    
    Only relevant for distributions involving two quantities.
    Generates one table per couple (quantity 1, quantity 2).
    Each table is a 360 x 360 array.
    
    Ex in birth-death case: table['ME-SA'][12][145] contains the number of persons with
    longitude of mercury at birth is between 12° and 13°
    and longitude of saturn at birth is between 145° and 146°
    
    NOTE: This only computes observed distributions. For dim2 data, expected distributions are not computed from control groups,
    but uses the method described in Didier Castille's article.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-04-13 23:14:10+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands;

use observe\model\Observe;
use observe\app\ICommand;
use observe\model\IStudy;
use observe\model\SqlitePlanets;
use observe\model\distrib\CsvDistrib;
use observe\model\distrib\EmptyDistribs;

use tiglib\filesystem\yieldFile;
use tiglib\filesystem\mkdir;
use tiglib\filesystem\file_put_contents;
use tiglib\time\seconds2HHMMSS;

class dim2 implements ICommand {
    
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
        // Prepare
        //
        $sqlite_planets = SqlitePlanets::getSqlite();
        SqlitePlanets::init_2days($sqlite_planets, $study->config['planets']);
        SqlitePlanets::init_1day($sqlite_planets, $study->config['planets']);
        $nDates = count($study->config['dates']);
        $nPlanets = count($study->config['planets']);
        $baseOutDir = $study->getObservedDirectory(); // ex: var/studies/death-fr/observed
        $inFile = 'compress.bzip2://' . $study->getDatafile(); // ex: compress.bzip2://var/studies/death-fr/data.csv.bz2
        //
        // Execute
        //
        $t1 = microtime(true);
        //
        // aspects
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = $study->config['dates'][$i]; // ex: birth
            echo "==== Processing $baseOutDir - $dateName - aspects\n";
            $res = EmptyDistribs::emptyDoubleDistrib_triangle_dim2($study->config['planets'], $study->config['planets'], 360);
            $loop = 0;
            foreach(yieldFile::loop($inFile) as $line){
                $fields = explode(Observe::CSV_SEP, trim($line));
                // here a convention is used: the dates in data.csv.bz2 are stored in the same order as in $study->config['dates']
                $planets = SqlitePlanets::getPlanets_1day($sqlite_planets, $fields[$i]);
                for($j=0; $j < $nPlanets; $j++){
                    for($k=$j+1; $k < $nPlanets; $k++){
                        $code1 = $study->config['planets'][$j];
                        $code2 = $study->config['planets'][$k];
                        $res["$code1-$code2"][floor($planets[$code1])][floor($planets[$code2])]++;
                    }
                }
                $loop++; if($loop % 100000 == 0) echo "$loop\n";;
            }
            $outDir = $baseOutDir . DS . $dateName . DS . 'aspects' . DS . 'dim2'; // ex: var/studies/death-fr/observed/birth/aspects/dim2
            mkdir::execute($outDir);
            // ex: $k = 'SO-MO' and $v = 2-dim array 360 x 360
            foreach($res as $k => $v){
                $outFile = $outDir . DS . $k . '.csv'; // ex: var/studies/death-fr/observed/birth/aspects/dim2/SO-MO.csv
                $csv = CsvDistrib::distrib2csv_dim2($v);
                file_put_contents::execute($outFile, $csv, false);
            }
        }
        //
        // interaspects
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j]; // ex: birth-death, mother-father etc.
                echo "==== Processing $baseOutDir - $dateName - interaspects\n";
                // if 11 planets, $res has 11 * 11 * 360 * 360 = 15 681 600 elements
                $res = EmptyDistribs::emptyDoubleDistrib_square_dim2($study->config['planets'], $study->config['planets'], 360);
                $loop = 0;
                foreach(yieldFile::loop($inFile) as $line){
                    $fields = explode(Observe::CSV_SEP, trim($line));
                    // here a convention is used: the dates in data.csv.bz2 are stored in the same order as in $study->config['dates']
                    [$planets1, $planets2] = SqlitePlanets::getPlanets_2days($sqlite_planets, $fields[$i], $fields[$j]);
                    foreach($planets1 as $planet1 => $lg1){
                        foreach($planets2 as $planet2 => $lg2){
                            $res["$planet1-$planet2"][floor($lg1)][floor($lg2)]++;
                        }
                    }
                    $loop++; if($loop % 100000 == 0) echo "$loop\n";;
                }
                $outDir = $baseOutDir . DS . $dateName . DS . 'interaspects' . DS . 'dim2'; // ex: var/studies/death-fr/observed/birth-death/interaspects/dim2
                mkdir::execute($outDir);
                // ex: $k = 'SO-SO' and $v = 2-dim array 360 x 360
                foreach($res as $k => $v){
                    $outFile = $outDir . DS . $k . '.csv'; // ex: var/studies/death-fr/observed/birth-death/interaspects/dim2/SO-SO.csv
                    $csv = CsvDistrib::distrib2csv_dim2($v);
                    file_put_contents::execute($outFile, $csv, false);
                }
            } // end loop on $j
        } // end loop on $i
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Execution time $dt s - $dth\n";
        return '';
    }
    
} // end class
