<?php
/******************************************************************************
    Main class to compute distributions.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-13 18:44:21+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\model\distrib;

use observe\model\Observe;
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
        $res = self::initializeDistributions($studyConfig);
        foreach($func() as $line){
            self::fillDistributionsWithLine($res, trim($line), $studyConfig);
        }
        return $res;
    }
    
    /**
        Fills the distributions of a study with one line containing dates.
        $res of the calling code is modified because passed by reference.
    **/
    public static function fillDistributionsWithLine(array &$res, string $line, array &$studyConfig): void {
        $nDates = count($studyConfig['dates']);
        $dates = explode(Observe::CSV_SEP, trim($line));
        $execArray = [];
        for($i=0; $i < count($dates); $i++){
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
                $age = diff::compute(new \DateTime($dates[$i]), new \DateTime($dates[$j]), $studyConfig['unit-distrib-age']);
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
        Initializes the distributions of a study.
        The knowledge of $studyConfig['date'] permits to deduce the distributions of type distrib1 and distrib2 to initialize.
    **/
    public static function initializeDistributions(array &$studyConfig): array {
        $res = [];
        $nDates = count($studyConfig['dates']);
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $studyConfig['dates'][$i];
            $res[$dateName] = EmptyDistribs::emptyDistrib1($studyConfig);
        }
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName1 = $studyConfig['dates'][$i];
                $dateName2 = $studyConfig['dates'][$j];
                $res["$dateName1-$dateName2"] = EmptyDistribs::emptyDistrib2($studyConfig);
            }
        }
        return $res;
    }
    
    /**
        Stores the distributions of a study in csv files.
        @param  $distribs   The dis
    **/
    public static function storeDistributions(string $baseDir, array &$distribs, array &$studyConfig): void {
        $nDates = count($studyConfig['dates']);
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $studyConfig['dates'][$i];
            $outDir = $baseDir . DS . $dateName;
            // aspects
            $dir = $outDir . DS . 'aspects';
            mkdir::execute($dir, 0755, true);
            foreach($distribs[$dateName]['aspects'] as $distribName => $distribValues){
                $filename = $dir . DS . $distribName . '.csv';
                $contents = CsvDistrib::distrib2csv($distribValues);
                file_put_contents($filename, $contents);
            }
exit;
            // planets
            $dir = $outDir . DS . 'planets';
            mkdir::execute($dir, 0755, true);
            // day
            // year
        }
exit;
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j];
                $outDir = $baseDir . DS . $dateName;
                mkdir::execute($outDir, 0755, true);
            }
        }
    }
    
} // end class
