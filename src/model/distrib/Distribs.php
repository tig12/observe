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
        self::$stmt_planets = self::$sqlite_planets->prepare('select ' . implode(',', $studyConfig['planets']) . ' from planet where day=:day');
        self::$codePlanets = $studyConfig['planets'];
        self::$nPlanets = count(self::$codePlanets);
        self::$initOK = true;
    }
    
    /** 
        Conductor of distribution omputation.
        @param  $func Function which yields the data whose distributions need to be computed.
    **/
    public static function computeDistributions(callable $func, array &$studyConfig) {
        if(!self::$initOK){
            self::init($studyConfig);
        }
        $res = self::initializeDistributions($studyConfig);
print_r($res); exit;
        foreach($func() as $line){
            self::fillDistributionsWithLine($res, trim($line), $studyConfig);
        }
    }
    
    /**
        Fills the distributions of a study with one line containing dates.
        $res of the calling code is modified because passed by reference.
    **/
    public static function fillDistributionsWithLine(array &$res, string $line, array &$studyConfig): void {
        $res = [];
        $n = count($studyConfig['dates']);
        $dates = explode(Observe::CSV_SEP, trim($line));
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $n; $i++){
            $name = $studyConfig['dates'][$i]; // "birth", "death", "mother", "father" etc.
echo "date = " . $dates[$i] . "\n";
echo "name = $name\n";
            self::$stmt_planets->execute([':day' => $dates[$i]]);
            $planets = self::$stmt_planets->fetch(\PDO::FETCH_ASSOC);
echo "\n"; print_r($planets); echo "\n";
echo "day = " . substr($dates[$i], 5) . "\n";
            // day
            $res[$name]['day'][substr($dates[$i], 5)]++;
echo "res['name']['day']\n"; print_r($res[$name]['day']); echo "\n";
            //year
            $y = substr($dates[$i], 0, 4);
            $res[$name]['year'][$y] = $res[$name]['year'][$y] + 1 ?? 1;
            // planets
            foreach($planets as $codePlanet => $longitude){
                $res[$name]['planets'][$codePlanet][floor($longitude)]++;
            }
echo "res['name']['planets']\n"; print_r($res[$name]['planets']); echo "\n";
            // aspects
            for($j=0; $j < self::$nPlanets; $j++){
                for($k=$j+1; $k < self::$nPlanets; $k++){
                    $res[$name]['aspects'][self::$codePlanets[$j] . '-' . self::$codePlanets[$k]][floor(mod360::compute($planets[self::$codePlanets[$j]] - $planets[self::$codePlanets[$k]]))]++;
                }
            }
echo "res['name']['aspects']\n"; print_r($res[$name]['aspects']); echo "\n";
break;
        }
exit;
        
        //
        // distributions of type distrib2
        //
        for($i=0; $i < $n; $i++){
            self::$stmt_planets->execute([':day' => $dates[$i]]);
            $planets1 = self::$stmt_planets->fetch(\PDO::FETCH_ASSOC);
            for($j=$i+1; $j < $n; $j++){
                $name = $studyConfig['dates'][$i] . '-' . $studyConfig['dates'][$j]; // "birth-death", "mother-father" etc.
                self::$stmt_planets->execute([':day' => $dates[$j]]);
                $planets2 = self::$stmt_planets->fetch(\PDO::FETCH_ASSOC);
                // age
                $res[$name]['age'] = diff::compute(new \DateTime($dates[$i], $dates[$j], $studyConfig['unit-distrib-age']));
                // interaspect
                for($k=0; $k < self::$nPlanets; $k++){
                    for($l=0; $l < self::$nPlanets; $l++){
                        $res[$name]['interaspects'][self::$codePlanets[$k] . '-' . self::$codePlanets[$l]][floor(mod360::compute($planets1[self::$codePlanets[$k]] - $planets2[self::$codePlanets[$l]]))]++;
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
        $n = count($studyConfig['dates']);
        // distributions of type distrib1
        for($i=0; $i < $n; $i++){
            $name = $studyConfig['dates'][$i];
            $res[$name] = EmptyDistrib::emptyDistrib1($studyConfig);
        }
        // distributions of type distrib2
        for($i=0; $i < $n; $i++){
            for($j=$i+1; $j < $n; $j++){
                $name1 = $studyConfig['dates'][$i];
                $name2 = $studyConfig['dates'][$j];
                $res["$name1-$name2"] = EmptyDistrib::emptyDistrib2($studyConfig);
            }
        }
        return $res;
    }
    
} // end class
