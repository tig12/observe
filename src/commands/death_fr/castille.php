<?php
/******************************************************************************
    
    Builds Castille distributions, as described in http://cura.free.fr/xx/18cas3fr.html
    These distributions can be seen as the same nature of observed distributions.
    
    === NOTE ===
    This code is is death_fr namespace only because of call to Death_fr::getSplitSubgroups()
    Should be logically placed in shared - and the logic of getSplitSubgroups() should be changed.
    === END NOTE ===
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-04-13 23:14:10+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\death_fr;

use observe\model\ICommand;
use observe\model\Observe;
use observe\model\Studies;
use observe\model\SqlitePlanets;
use observe\model\distrib\CsvDistrib;

use tiglib\filesystem\yieldFile;
use tiglib\filesystem\mkdir;
use tiglib\filesystem\file_put_contents;
use tiglib\time\seconds2HHMMSS;

class castille implements ICommand {
    
    private static \PDO $sqlite_planets;
    private static \PDOStatement $stmt_planets;
    
    private static function init(array &$studyConfig): void {
        self::$sqlite_planets = SqlitePlanets::getSqlite();
        $planets = implode(',', $studyConfig['planets']);
        $days = '';
        for($i=0; $i < count($studyConfig['dates']); $i++){
            $days .= ":d$i,";
        }
        $days = substr($days, 0, -1);
        // select SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planet where day in(:d0,:d1)
        self::$stmt_planets = self::$sqlite_planets->prepare("select $planets from planet where day in($days)");
    }
    
    /** 
        Called by Studies::runCommand()
    **/
    public static function execute(array &$studyConfig, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe death-fr castille <split>\n"
            . "<split> can be:\n  - " . implode("\n  - ", $studyConfig['splits']) . "\n";
        if(count($params) != 1){
            return "MISSING PARAMETER split.\n$usage";
        }
        $split = $params[0];
        if(!in_array($split, $studyConfig['splits'])){
            return "INVALID PARAMETER split: \"$split\".\n$usage";
        }
        //
        // Prepare
        //
        self::init($studyConfig);
        $nDates = count($studyConfig['dates']);
        //
        // Execute
        //
        $t1 = microtime(true);
        $splitSubgroups = Death_fr::getSplitSubgroups($split);
        foreach($splitSubgroups as $subgroup){
            //
            // prepare the out directories to avoid doing it during main loop on $inFile.
            //
            $workDir = Studies::getSplitDirectory($studyConfig, $split) . DS . $subgroup; // ex: var/studies/death-fr/split-full/01--0-200years
            $outDirBase = $workDir . DS . 'castille'; // ex: var/studies/death-fr/split-full/01--0-200years/castille
            mkdir::execute($outDirBase);
            //
            // main loop
            //
            for($i=0; $i < $nDates; $i++){
                for($j=$i+1; $j < $nDates; $j++){
                    $dateName = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j]; // birth-death, mother-father etc.
                    echo "======= Processing $workDir - $dateName =======\n";
                    $res = self::emptyDistribs($studyConfig['planets'], 360); // 11 * 11 * 360 * 360 = 15 681 600 elements
                    $inFile = 'compress.bzip2://' . $workDir . DS . 'data.csv.bz2'; // ex: compress.bzip2://var/studies/death-fr/split-full/01--0-200years/data.csv.bz2
                    $k = 0;
                    foreach(yieldFile::loop($inFile) as $line){
                        $fields = explode(Observe::CSV_SEP, trim($line));
                        // here a cnvention is used: the dates in data.csv.bz2 are stored in the same order as in $studyConfig['dates']
                        $date1 = $fields[$i];
                        $date2 = $fields[$j];
                        [$planets1, $planets2] = self::getPlanets($date1, $date2);
                        foreach($planets1 as $planet1 => $lg1){
                            foreach($planets2 as $planet2 => $lg2){
// echo "date1 = $date1\n";
// echo "date2 = $date2\n";
// echo "$planet1-$planet2\n";
// echo "lg1 = $lg1\n";
// echo "lg2 = $lg2\n";
                                $res["$planet1-$planet2"][floor($lg1)][floor($lg2)]++;
                            }
                        }
                        $k++; if($k % 10000 == 0) echo "$k\n";;
                    } // end main loop on $inFile
                    $outDir = $outDirBase . DS . $dateName; // ex: var/studies/death-fr/split-full/01--0-200years/castille/birth-death
                    mkdir::execute($outDir);
                    // ex: $k = 'SO-SO' and $v = 2-dim array 360 x 360
                    foreach($res as $k => $v){
                        $outFile = $outDir . DS . $k . '.csv'; // ex: var/studies/death-fr/split-full/01--0-200years/castille/birth-death/SO-SO.csv
                        $csv = CsvDistrib::distrib2csv2dim($v);
                        file_put_contents::execute($outFile, $csv, false);
                    }
                } // end loop on $j
            } // end loop on $i
        } // end loop on $subgroup
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Execution time $dt s - $dth\n";
        return '';
    }
    
    /** 
        select SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planet where day in(:d0,:d1)
        @param  $date1 and $date2   format YYYY-MM-DD
        @return array of 2 elements containing the planets for $date1 and $date2
                [
                    0 => ['SO' => 123.654, ... 'MO' => 321.654],
                    1 => ['SO' => 23.774, ... 'MO' => 54.874],
                ]
    **/
    private static function getPlanets(string $date1, string $date2): array {
        self::$stmt_planets->execute([ 'd0' => $date1, 'd1' => $date2 ]);
        $planets = self::$stmt_planets->fetchAll(\PDO::FETCH_ASSOC);
        if(count($planets) == 1){
            // particular case: death day = birth day
            $planets[1] = $planets[0];
        }
        return $planets;
    }
    
    /** 
        Builds an empty 3-dim array:
        [
            'SO-SO' => [
                0   => [0 => 0,  ... 359 => 0],
                ...
                359 => [0 => 0,  ... 359 => 0],
            ],
            'SO-MO' => [
                0   => [0 => 0,  ... 359 => 0],
                ...
                359 => [0 => 0,  ... 359 => 0],
            ],
            ...
        ]
    **/
    private static function emptyDistribs(array $codePlanets, int $N): array {
        $res = [];
        foreach($codePlanets as $code1){
            foreach($codePlanets as $code2){
                $res["$code1-$code2"] = array_fill(0, $N, array_fill(0, $N, 0));
            }
        }
        return $res;
    }
    
} // end class
