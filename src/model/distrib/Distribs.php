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
    
    private static function init(array $dateNames, array $planetCodes): void {
        self::$sqlite_planets = SqlitePlanets::getSqlite();
        $planets = implode(',', $planetCodes);
        $days = '';
        for($i=0; $i < count($dateNames); $i++){
            $days .= ":d$i,";
        }
        $days = substr($days, 0, -1);
        // ex: select day,SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planet where day in(:d0,:d1)
        self::$stmt_planets = self::$sqlite_planets->prepare("select day,$planets from planet where day in($days)");
        self::$codePlanets = $planetCodes;
        self::$nPlanets = count(self::$codePlanets);
        self::$initOK = true;
    }
    
    /** 
        Conductor of distribution computation.
        @param  $func Function which yields the data whose distributions need to be computed.
    **/
    public static function computeDistributions(callable $func, array $dateNames, array $planetCodes): array {
        if(!self::$initOK){
            self::init($dateNames, $planetCodes);
        }
        $res = EmptyDistribs::initializeDistributions($dateNames, $planetCodes);
        foreach($func() as $dates){
            self::fillDistributionsWithLine($res, $dates, $dateNames, $planetCodes);
        }
        return $res;
    }
    
    /**
        Fills the distributions of a study with one line containing dates.
        $res of the calling code is modified because passed by reference.
    **/
    public static function fillDistributionsWithLine(array &$res, array $dates, array $dateNames, array $planetCodes): void {
        $nDates = count($dates);
        $execArray = [];
        for($i=0; $i < $nDates; $i++){
            $execArray[":d$i"] = $dates[$i];
        }
        // Note: we must select the day from database and perform this loop
        // because some dates can be equal, and sql returns less planets than dates
        self::$stmt_planets->execute($execArray);
        $rows = self::$stmt_planets->fetchAll(\PDO::FETCH_ASSOC);
        $planets = [];
        for($i=0; $i < $nDates; $i++){
            if($dates[$i] == ''){ // missing date
                $planets[] = [];
                continue;
            }
            foreach($rows as $row){
                if($row['day'] == $dates[$i]){
                    $planets[] = array_slice($row, 1, preserve_keys:true);
                    break;
                }
            }
        }
        //
        // distributions of type distrib1
        //
        for($i=0; $i < $nDates; $i++){
            if($dates[$i] == ''){
                continue; // missing date
            }
            $dateName = $dateNames[$i]; // "birth", "death", "mother", "father" etc.
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
            if($dates[$i] == ''){
                continue; // missing date
            }
            for($j=$i+1; $j < $nDates; $j++){
                if($dates[$j] == ''){
                    continue; // missing date
                }
                $dateName = $dateNames[$i] . '-' . $dateNames[$j]; // birth-death, mother-father etc.
                // age
                $diff = diff::compute_all(new \DateTime($dates[$i]), new \DateTime($dates[$j]));
//                if(!isset($res[$dateName]['age']['dim1']['age-D'][$diff['D']])){
//                    $res[$dateName]['age']['dim1']['age-D'][$diff['D']] = 0;
//                }
                if(!isset($res[$dateName]['age']['dim1']['age-M'][$diff['M']])){
                    $res[$dateName]['age']['dim1']['age-M'][$diff['M']] = 0;
                }
                if(!isset($res[$dateName]['age']['dim1']['age-Y'][$diff['Y']])){
                    $res[$dateName]['age']['dim1']['age-Y'][$diff['Y']] = 0;
                }
//                $res[$dateName]['age']['dim1']['age-D'][$diff['D']]++;
                $res[$dateName]['age']['dim1']['age-M'][$diff['M']]++;
                $res[$dateName]['age']['dim1']['age-Y'][$diff['Y']]++;
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
    public static function storeDistributions(string $baseDir, array &$distribs, array $dateNames): void {
        $nDates = count($dateNames);
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $dateNames[$i]; // ex: birth
            $outDir_date = $baseDir . DS . $dateName; // ex: var/studies/death-fr/split-all/01--0-150years/observed/birth
            mkdir::execute($outDir_date);
            // positions of planets
            $dir = $outDir_date . DS . 'positions'; // ex: var/studies/death-fr/observed/birth/positions
            mkdir::execute($dir);
            foreach($distribs[$dateName]['positions'] as $distribName => $distribValues){
                $filename = $dir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/observed/birth/positions/SO.csv
                $contents = CsvDistrib::distrib2csv_dim1($distribValues);
                file_put_contents($filename, $contents);
            }
            // aspects
            $dir = $outDir_date . DS . 'aspects' . DS . 'dim1'; // ex: var/studies/death-fr/observed/birth/aspects/dim1
            mkdir::execute($dir);
            foreach($distribs[$dateName]['aspects']['dim1'] as $distribName => $distribValues){
                $filename = $dir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/observed/birth/aspects/dim1/SO-MO.csv
                $contents = CsvDistrib::distrib2csv_dim1($distribValues);
                file_put_contents($filename, $contents);
            }
            // day and year
            ksort($distribs[$dateName]['year']);
            foreach(['day', 'year'] as $distribName){
                $filename = $outDir_date . DS . $distribName . '.csv'; // ex: var/studies/death-fr/observed/birth/day.csv
                $distribValues = $distribs[$dateName][$distribName];
                $contents = CsvDistrib::distrib2csv_dim1($distribValues);
                file_put_contents($filename, $contents);
            }
        }
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $dateNames[$i] . '-' . $dateNames[$j]; // ex: birth-death
                $outDir_date = $baseDir . DS . $dateName;
                mkdir::execute($outDir_date);
                // interaspects
                $dir = $outDir_date . DS . 'interaspects' . DS . 'dim1'; // ex: var/studies/death-fr/observed/birth-death/interaspects/dim1
                mkdir::execute($dir);
                foreach($distribs[$dateName]['interaspects']['dim1'] as $distribName => $distribValues){
                    $filename = $dir . DS . $distribName . '.csv'; // ex: var/studies/death-fr/observed/birth-death/interaspects/dim1/SO-SO.csv
                    $contents = CsvDistrib::distrib2csv_dim1($distribValues);
                    file_put_contents($filename, $contents);
                }
                // age
                $dir = $outDir_date . DS . 'age' . DS . 'dim1'; // ex: var/studies/death-fr/observed/birth-death/age/dim1
                mkdir::execute($dir);
//                foreach(['D', 'M', 'Y'] as $unit){
                foreach(['M', 'Y'] as $unit){
                    ksort($distribs[$dateName]['age']['dim1']["age-$unit"]);
                    $filename = $dir . DS . "age-$unit.csv"; // ex: var/studies/death-fr/observed/birth-death/age/dim1/age-M.csv
                    $distribValues = $distribs[$dateName]['age']['dim1']["age-$unit"];
                    $contents = CsvDistrib::distrib2csv_dim1($distribValues);
                    file_put_contents($filename, $contents);
                }
            } // end loop on $j
        } // end loop on $i
    }
    
    /**
        Loads the distributions of a study from csv files.
        $baseDir is supposed to be structured with distributions of type distrib1 and distrib2 (no verification on the existence of the csv files).
    **/
    public static function loadDistributions(string $baseDir, array $dateNames, array $planetNames): array {
        $res = EmptyDistribs::initializeDistributions($dateNames, $planetNames);
        $nDates = count($dateNames);
        // distributions of type distrib1
        for($i=0; $i < $nDates; $i++){
            $dateName = $dateNames[$i]; // ex: birth
            $inDir_date = $baseDir . DS . $dateName; // ex: var/studies/death-fr/observed/birth
            // planet positions
            $dir = $inDir_date . DS . 'positions'; // ex: var/studies/death-fr/observed/birth/positions
            $filenames = glob($dir . DS . '*.csv');
            foreach($filenames as $filename){
                $res[$dateName]['positions'][basename($filename, '.csv')] = CsvDistrib::csv2distrib_dim1($filename);
            }
            // aspects
            $dir = $inDir_date . DS . 'aspects' . DS . 'dim1'; // ex: var/studies/death-fr/observed/birth/aspects/dim1
            $filenames = glob($dir . DS . '*.csv');
            foreach($filenames as $filename){
                $res[$dateName]['aspects']['dim1'][basename($filename, '.csv')] = CsvDistrib::csv2distrib_dim1($filename);
            }
            // day and year
            foreach(['day', 'year'] as $distribName){
                $filename = $inDir_date . DS . $distribName . '.csv'; // ex: var/studies/death-fr/observed/birth/day.csv
                $res[$dateName][$distribName] = CsvDistrib::csv2distrib_dim1($filename);
            }
        }
        // distributions of type distrib2
        for($i=0; $i < $nDates; $i++){
            for($j=$i+1; $j < $nDates; $j++){
                $dateName = $dateNames[$i] . '-' . $dateNames[$j]; // ex: birth-death
                $inDir_date = $baseDir . DS . $dateName;
                // interaspects
                $dir = $inDir_date . DS . 'interaspects' . DS . 'dim1'; // ex: var/studies/death-fr/observed/birth-death/interaspects/dim1
                $filenames = glob($dir . DS . '*.csv');
                foreach($filenames as $filename){
                    $res[$dateName]['interaspects']['dim1'][basename($filename, '.csv')] = CsvDistrib::csv2distrib_dim1($filename);
                }
                // age
                $dir = $inDir_date . DS . 'age' . DS . 'dim1'; // ex: var/studies/death-fr/observed/birth-death/age/dim1
//                foreach(['D', 'M', 'Y'] as $unit){
                foreach(['M', 'Y'] as $unit){
                    $filename = $dir . DS . "age-$unit.csv"; // ex: var/studies/death-fr/observed/birth-death/age/dim1/age-M.csv
                    $res[$dateName]['age']['dim1']["age-$unit"] = CsvDistrib::csv2distrib_dim1($filename);
                }
            } // end loop on $j
        } // end loop on $i
        return $res;
    }
    
} // end class
