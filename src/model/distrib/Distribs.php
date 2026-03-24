<?php
/******************************************************************************
    Main class to compute distributions.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-13 18:44:21+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\model\distrib;

use observe\model\SqlitePlanets;
use tiglib\time\diff;
use tiglib\math\mod360;
use tiglib\filesystem\mkdir;

class Distribs {
    
    private static $initOK = false;
    
    private static \PDO $sqlite_planets;
    
    private static \PDOStatement $stmt_planets;

    private static array $codePlanets;
    
    private static int $nPlanets;
    
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
        self::$codePlanets = $studyConfig['planets'];
        self::$nPlanets = count(self::$codePlanets);
        self::$initOK = true;
    }
    
    /** 
        Conductor of distribution omputation.
        @param  $func Function which yields the data whose distributions need to be computed.
    **/
    public static function computeDistributions(callable $func, array &$studyConfig): array {
        if(!self::$initOK){
            self::init($studyConfig);
        }
        $res = EmptyDistribs::initializeDistributions($studyConfig);
        foreach($func() as $dates){
            self::fillDistributionsWithLine($res, $dates, $studyConfig);
        }
        return $res;
    }
    
    /**
        Fills the distributions of a study with one line containing dates.
        $res of the calling code is modified because passed by reference.
    **/
    public static function fillDistributionsWithLine(array &$res, array $dates, array &$studyConfig): void {
        $nDates = count($dates);
        $execArray = [];
        for($i=0; $i < $nDates; $i++){
            $execArray[":d$i"] = $dates[$i];
        }
        self::$stmt_planets->execute($execArray);
        $planets = self::$stmt_planets->fetchAll(\PDO::FETCH_ASSOC);
        if(count($planets) == 1){
            // particular case: death day = birth day
            $planets[1] = $planets[0];
        }
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $dateName = $studyConfig['dates'][$i]; // "birth", "death", "mother", "father" etc.
            // day
            $res[$dateName]['day'][substr($dates[$i], 5)]++;
            //year
            $y = substr($dates[$i], 0, 4);
            if(!isset($res[$dateName]['year'][$y])){
                $res[$dateName]['year'][$y] = 0;
            }
            $res[$dateName]['year'][$y]++;
            // planets
            foreach($planets[$i] as $codePlanet => $longitude){
                $res[$dateName]['planets'][$codePlanet][floor($longitude)]++;
            }
            // aspects
            for($j=0; $j < self::$nPlanets; $j++){
                for($k=$j+1; $k < self::$nPlanets; $k++){
                    // Take $planets[$i] to have the aspects between planets of $dates[$i]
                    // Warning: mod360::compute($k - $j) to have the angle from planet j to planet k
                    $res[$dateName]['aspects'][self::$codePlanets[$j] . '-' . self::$codePlanets[$k]][floor(mod360::compute($planets[$i][self::$codePlanets[$k]] - $planets[$i][self::$codePlanets[$j]]))]++;
                }
            }
        }
        //
        // distributions of type distrib2
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j]; // "birth-death", "mother-father" etc.
                // age
                $age = diff::compute(new \DateTime($dates[$i]), new \DateTime($dates[$j]), $studyConfig['distrib-age-unit']);
                if(!isset($res[$dateName]['age'][$age])){
                    $res[$dateName]['age'][$age] = 0;
                }
                $res[$dateName]['age'][$age]++;
                // interaspect
                for($k=0; $k < self::$nPlanets; $k++){ // $k loop on $planets[$i]
                    for($l=0; $l < self::$nPlanets; $l++){ // $l loop on $planets[$j]
                        // Take $planets[$i] and $planets[$j] to have the interaspects between planets of $dates[$i] and $dates[$j]
                        // Warning; mod360::compute($l - $k) to have the angle from planet k to planet l
                        $res[$dateName]['interaspects'][self::$codePlanets[$k] . '-' . self::$codePlanets[$l]][floor(mod360::compute($planets[$j][self::$codePlanets[$l]] - $planets[$i][self::$codePlanets[$k]]))]++;
                    }
                }
            }
        }
    }
    
    /**
        Stores the distributions of a study in csv files.
        @param  $distribs   The distributions to store
    **/
    public static function storeDistributions(string $baseDir, array &$distribs, array &$studyConfig): void {
        $nDates = count($studyConfig['dates']);
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $studyConfig['dates'][$i]; // ex: birth
            $outDir = $baseDir . DS . $dateName; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth
            // aspects and planets
            foreach(['aspects', 'planets'] as $distribType){
                $dir = $outDir . DS . $distribType; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth/aspects
                mkdir::execute($dir, 0755, true);
                foreach($distribs[$dateName][$distribType] as $distribName => $distribValues){
                    $filename = $dir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth/aspects/SO-MO.csv
                    $contents = CsvDistrib::distrib2csv($distribValues);
                    file_put_contents($filename, $contents);
                }
            }
            // day and year
            ksort($distribs[$dateName]['year']);
            foreach(['day', 'year'] as $distribName){
                $filename = $outDir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth/day.csv
                $distribValues = $distribs[$dateName][$distribName];
                $contents = CsvDistrib::distrib2csv($distribValues);
                file_put_contents($filename, $contents);
            }
        }
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j]; // ex: birth-death
                $outDir = $baseDir . DS . $dateName;
                mkdir::execute($outDir, 0755, true);
                // interaspects
                foreach(['interaspects'] as $distribType){
                    $dir = $outDir . DS . $distribType; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth-death/interaspects
                    mkdir::execute($dir, 0755, true);
                    foreach($distribs[$dateName][$distribType] as $distribName => $distribValues){
                        $filename = $dir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth-death/interaspects/SO-SO.csv
                        $contents = CsvDistrib::distrib2csv($distribValues);
                        file_put_contents($filename, $contents);
                    }
                }
                // age
                ksort($distribs[$dateName]['age']);
                foreach(['age'] as $distribName){
                    $filename = $outDir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth-death/age.csv
                    $distribValues = $distribs[$dateName][$distribName];
                    $contents = CsvDistrib::distrib2csv($distribValues);
                    file_put_contents($filename, $contents);
                }
            } // end loop on $j
        } // end loop on $i
    }
    
    /**
        Loads the distributions of a study from csv files.
        $baseDir is supposed to be structured wuth distributions of type distrib1 and distrib2 (no verification on the existence of the csv files).
    **/
    public static function loadDistributions(string $baseDir, array &$studyConfig): array {
        $res = EmptyDistribs::initializeDistributions($studyConfig);
        $nDates = count($studyConfig['dates']);
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $studyConfig['dates'][$i]; // ex: birth
            $inDir = $baseDir . DS . $dateName; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth
            // aspects and planets
            foreach(['aspects', 'planets'] as $distribType){
                $dir = $inDir . DS . $distribType; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth/aspects
                $filenames = glob($dir . DS . '*.csv');
                foreach($filenames as $filename){
                    $res[$dateName][$distribType][basename($filename, '.csv')] = CsvDistrib::csv2distrib($filename, false);
                }
            }
            // day and year
            foreach(['day', 'year'] as $distribName){
                $filename = $inDir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth/day.csv
                $res[$dateName][$distribName] = CsvDistrib::csv2distrib($filename, false);
            }
        }
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j]; // ex: birth-death
                $inDir = $baseDir . DS . $dateName;
                // interaspects
                foreach(['interaspects'] as $distribType){
                    $dir = $inDir . DS . $distribType; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth-death/interaspects
                    $filenames = glob($dir . DS . '*.csv');
                    foreach($filenames as $filename){
                        $res[$dateName][$distribType][basename($filename, '.csv')] = CsvDistrib::csv2distrib($filename, false);
                    }
                }
                // age
                foreach(['age'] as $distribName){
                    $filename = $inDir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth-death/age.csv
                    $res[$dateName][$distribName] = CsvDistrib::csv2distrib($filename, false);
                }
            } // end loop on $j
        } // end loop on $i
        return $res;
    }
    
} // end class
