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
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            $name = $studyConfig['dates'][$i]; // "birth", "death", "mother", "father" etc.
            // day
            $res[$name]['day'][substr($dates[$i], 5)]++;
            //year
            $y = substr($dates[$i], 0, 4);
            if(!isset($res[$name]['year'][$y])){
                $res[$name]['year'][$y] = 0;
            }
            $res[$name]['year'][$y]++;
            // planets
            foreach($planets[$i] as $codePlanet => $longitude){
                $res[$name]['planets'][$codePlanet][floor($longitude)]++;
            }
            // aspects
            for($j=0; $j < self::$nPlanets; $j++){
                for($k=$j+1; $k < self::$nPlanets; $k++){
                    // Take $planets[$i] to have the aspects between planets of $dates[$i]
                    // Warning: mod360::compute($k - $j) to have the angle from planet j to planet k
                    $res[$name]['aspects'][self::$codePlanets[$j] . '-' . self::$codePlanets[$k]][floor(mod360::compute($planets[$i][self::$codePlanets[$k]] - $planets[$i][self::$codePlanets[$j]]))]++;
                }
            }
        }
        //
        // distributions of type distrib2
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $name = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j]; // "birth-death", "mother-father" etc.
                // age
                $age = diff::compute(new \DateTime($dates[$i]), new \DateTime($dates[$j]), $studyConfig['unit-distrib-age']);
                if(!isset($res[$name]['age'][$age])){
                    $res[$name]['age'][$age] = 0;
                }
                $res[$name]['age'][$age]++;
                // interaspect
                for($k=0; $k < self::$nPlanets; $k++){ // $k loop on $planets[$i]
                    for($l=0; $l < self::$nPlanets; $l++){ // $l loop on $planets[$j]
                        // Take $planets[$i] and $planets[$j] to have the interaspects between planets of $dates[$i] and $dates[$j]
                        // Warning; mod360::compute($l - $k) to have the angle from planet k to planet l
                        $res[$name]['interaspects'][self::$codePlanets[$k] . '-' . self::$codePlanets[$l]][floor(mod360::compute($planets[$j][self::$codePlanets[$l]] - $planets[$i][self::$codePlanets[$k]]))]++;
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
            $name = $studyConfig['dates'][$i];
            $res[$name] = EmptyDistribs::emptyDistrib1($studyConfig);
        }
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $name1 = $studyConfig['dates'][$i];
                $name2 = $studyConfig['dates'][$j];
                $res["$name1-$name2"] = EmptyDistribs::emptyDistrib2($studyConfig);
            }
        }
        return $res;
    }
    
} // end class
