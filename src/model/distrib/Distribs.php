<?php
/******************************************************************************
    
    Main class to compute distributions.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-03-13 18:44:21+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\model\distrib;

use observe\model\IStudy;
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
    
    private static function init(IStudy $study): void {
        self::$sqlite_planets = SqlitePlanets::getSqlite();
        $planets = implode(',', $study->config['planets']);
        $days = '';
        for($i=0; $i < count($study->config['dates']); $i++){
            $days .= ":d$i,";
        }
        $days = substr($days, 0, -1);
        // select SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planet where day in(:d0,:d1)
        self::$stmt_planets = self::$sqlite_planets->prepare("select $planets from planet where day in($days)");
        self::$codePlanets = $study->config['planets'];
        self::$nPlanets = count(self::$codePlanets);
        self::$initOK = true;
    }
    
    /** 
        Conductor of distribution computation.
        @param  $func Function which yields the data whose distributions need to be computed.
    **/
    public static function computeDistributions(callable $func, IStudy $study): array {
        if(!self::$initOK){
            self::init($study);
        }
        $res = EmptyDistribs::initializeDistributions($study->config['dates'], $study->config['planets']);
        foreach($func() as $dates){
            self::fillDistributionsWithLine($res, $dates, $study);
        }
        return $res;
    }
    
    /**
        Fills the distributions of a study with one line containing dates.
        $res of the calling code is modified because passed by reference.
    **/
    public static function fillDistributionsWithLine(array &$res, array $dates, IStudy $study): void {
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
            $dateName = $study->config['dates'][$i]; // "birth", "death", "mother", "father" etc.
            // day
            $res[$dateName]['day'][substr($dates[$i], 5)]++;
            //year
            $y = substr($dates[$i], 0, 4);
            if(!isset($res[$dateName]['year'][$y])){
                $res[$dateName]['year'][$y] = 0;
            }
            $res[$dateName]['year'][$y]++;
            // planet positions
            foreach($planets[$i] as $codePlanet => $longitude){
                $res[$dateName]['positions'][$codePlanet][floor($longitude)]++;
            }
            // aspects
            for($j=0; $j < self::$nPlanets; $j++){
                for($k=$j+1; $k < self::$nPlanets; $k++){
                    $code = self::$codePlanets[$j] . '-' . self::$codePlanets[$k];
                    // Take $planets[$i] to have the aspects between planets of $dates[$i]
                    // Warning: mod360::compute($k - $j) to have the angle from planet j to planet k
                    $angle = floor(mod360::compute($planets[$i][self::$codePlanets[$k]] - $planets[$i][self::$codePlanets[$j]]));
                    $res[$dateName]['aspects']['dim1'][$code][$angle]++;
                }
            }
        }
        //
        // distributions of type distrib2
        //
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j]; // birth-death, mother-father etc.
                // age
                $age = diff::compute(new \DateTime($dates[$i]), new \DateTime($dates[$j]), $study->config['distrib-age-unit']);
                if(!isset($res[$dateName]['age-dim1'][$age])){
                    $res[$dateName]['age-dim1'][$age] = 0;
                }
                $res[$dateName]['age-dim1'][$age]++;
                // interaspects
                for($k=0; $k < self::$nPlanets; $k++){ // $k loop on $planets[$i]
                    for($l=0; $l < self::$nPlanets; $l++){ // $l loop on $planets[$j]
                        $code = self::$codePlanets[$k] . '-' . self::$codePlanets[$l];
                        // Take $planets[$i] and $planets[$j] to have the interaspects between planets of $dates[$i] and $dates[$j]
                        // Warning; mod360::compute($l - $k) to have the angle from planet k to planet l
                        $angle = floor(mod360::compute($planets[$j][self::$codePlanets[$l]] - $planets[$i][self::$codePlanets[$k]]));
                        $res[$dateName]['interaspects']['dim1'][$code][$angle]++;
                    }
                }
            }
        }
    }
    
    /**
        Stores the distributions of a study in csv files.
        @param  $distribs   The distributions to store
    **/
    public static function storeDistributions(string $baseDir, array &$distribs, IStudy $study): void {
        $nDates = count($study->config['dates']);
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $study->config['dates'][$i]; // ex: birth
            $outDir = $baseDir . DS . $dateName; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth
            // positions of planets
            $dir = $outDir . DS . 'positions'; // ex: var/studies/death-fr/observed/birth/positions
            mkdir::execute($dir);
            foreach($distribs[$dateName]['positions'] as $distribName => $distribValues){
                $filename = $dir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/observed/birth/positions/SO.csv
                $contents = CsvDistrib::distrib2csv($distribValues);
                file_put_contents($filename, $contents);
            }
            // aspects
            $dir = $outDir . DS . 'aspects' . DS . 'dim1'; // ex: var/studies/death-fr/observed/birth/aspects/dim1
            mkdir::execute($dir);
            foreach($distribs[$dateName]['aspects']['dim1'] as $distribName => $distribValues){
                $filename = $dir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/observed/birth/aspects/dim1/SO-MO.csv
                $contents = CsvDistrib::distrib2csv($distribValues);
                file_put_contents($filename, $contents);
            }
            // day and year
            ksort($distribs[$dateName]['year']);
            foreach(['day', 'year'] as $distribName){
                $filename = $outDir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/observed/birth/day.csv
                $distribValues = $distribs[$dateName][$distribName];
                $contents = CsvDistrib::distrib2csv($distribValues);
                file_put_contents($filename, $contents);
            }
        }
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j]; // ex: birth-death
                $outDir = $baseDir . DS . $dateName;
                mkdir::execute($outDir);
                // interaspects
                $dir = $outDir . DS . 'interaspects' . DS . 'dim1'; // ex: var/studies/death-fr/observed/birth-death/interaspects/dim1
                mkdir::execute($dir);
                foreach($distribs[$dateName]['interaspects']['dim1'] as $distribName => $distribValues){
                    $filename = $dir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/observed/birth-death/interaspects/dim1/SO-SO.csv
                    $contents = CsvDistrib::distrib2csv($distribValues);
                    file_put_contents($filename, $contents);
                }
                // age
                ksort($distribs[$dateName]['age-dim1']);
                $filename = $outDir . DS . 'age-dim1.csv'; // ex: var/studies/death-fr/observed/birth-death/age-dim1.csv
                $distribValues = $distribs[$dateName]['age-dim1'];
                $contents = CsvDistrib::distrib2csv($distribValues);
                file_put_contents($filename, $contents);
            } // end loop on $j
        } // end loop on $i
    }
    
    /**
        Loads the distributions of a study from csv files.
        $baseDir is supposed to be structured wuth distributions of type distrib1 and distrib2 (no verification on the existence of the csv files).
    **/
    public static function loadDistributions(string $baseDir, IStudy $study): array {
        $res = EmptyDistribs::initializeDistributions($study->config['dates'], $study->config['planets']);
        $nDates = count($study->config['dates']);
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $study->config['dates'][$i]; // ex: birth
            $inDir = $baseDir . DS . $dateName; // ex: var/studies/death-fr/observed/birth
            // planet positions
            $dir = $inDir . DS . 'positions'; // ex: var/studies/death-fr/observed/birth/positions
            $filenames = glob($dir . DS . '*.csv');
            foreach($filenames as $filename){
                $res[$dateName]['positions'][basename($filename, '.csv')] = CsvDistrib::csv2distrib($filename);
            }
            // aspects
            $dir = $inDir . DS . 'aspects' . DS . 'dim1'; // ex: var/studies/death-fr/observed/birth/aspects/dim1
            $filenames = glob($dir . DS . '*.csv');
            foreach($filenames as $filename){
                $res[$dateName]['aspects']['dim1'][basename($filename, '.csv')] = CsvDistrib::csv2distrib($filename);
            }
            // day and year
            foreach(['day', 'year'] as $distribName){
                $filename = $inDir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/observed/birth/day.csv
                $res[$dateName][$distribName] = CsvDistrib::csv2distrib($filename);
            }
        }
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j]; // ex: birth-death
                $inDir = $baseDir . DS . $dateName;
                // interaspects
                $dir = $inDir . DS . 'interaspects' . DS . 'dim1'; // ex: var/studies/death-fr/observed/birth-death/interaspects/dim1
                $filenames = glob($dir . DS . '*.csv');
                foreach($filenames as $filename){
                    $res[$dateName]['interaspects']['dim1'][basename($filename, '.csv')] = CsvDistrib::csv2distrib($filename);
                }
                // age
                $filename = $inDir . DS  . 'age-dim1.csv'; // ex: var/studies/death-fr/observed/birth-death/age-dim1.csv
                $res[$dateName]['age-dim1'] = CsvDistrib::csv2distrib($filename);
            } // end loop on $j
        } // end loop on $i
        return $res;
    }
    
} // end class
