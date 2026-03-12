<?php
/******************************************************************************
    Computes planetary positions for a date range.
    
    Parameters defined in config.yml, in section 'one-day-ephemeris'
    
    Stores the results in var/tmp/planets.sqlite3 (path defined in config.yml, entry 'one-day-ephemeris.sqlite-planets')
    
    @license    GPL
    @history    2026-02-13 20:51:50+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands;

use observe\app\Config;
use tiglib\time\yearRange;
use tiglib\time\daysOfYear;
use tiglib\time\seconds2HHMMSS;
use tigeph\model\IAA;
use tigeph\ephem\meeus1\Meeus1;

class prepareAstro {
    /** 
        @return Error message if problem, empty message if ok.
    **/
    public static function execute(array $params): string {
        //
        // Check parameters
        //
        $usage = "Usage: php run-observe.php prepare planets <date or date range>\n"
            . "Ex: php run-observe.php prepare planets 1970\n"
            . "    php run-observe.php prepare planets 1970-1985\n";
        // optional parameter to specify dates to compute
        if(count($params) == 0){
            return "MISSING PARAMETER: you must specify date range\n$usage";
        }
        if(count($params) > 1){
            return "USELESS PARAMETER: \"{$params[1]}\"\n$usage";
        }
        $years = yearRange::compute($params[0]);
        if($years === false){
            return "INVALID PARAMETER year range: \"{$params[0]}\"\n";
        }
        //
        // Check parameters
        //
        $planets = Config::$data['one-day-ephemeris']['planets']; // = ['SO', 'MO', 'ME', 'VE', 'MA', 'JU', 'SDA', 'UR', 'NE', 'PL', 'NN']
        //
        // Initialize sqlite database
        //
        if(!isset(Config::$data['one-day-ephemeris']['sqlite-planets'])){
            return "MISSING PARAMETER 'one-day-ephemeris.sqlite-planets' in config.yml\n";
        }
        $sqlite = self::initalizeSqlite(Config::$data['one-day-ephemeris']['sqlite-planets'], $planets);
        //
        // compute planet positions and store in sqlite
        //
        $t1 = microtime(true);
        $tigephPlanets = IAA::iaa2tigeph($planets);
        // insert into planet(day,SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN) values(:day,:SO,:MO,:ME,:VE,:MA,:JU,:SA,:UR,:NE,:PL,:NN)
        $sql = 'insert into planet(day,' . implode(',', $planets) .') values(:day,:' . implode(',:', $planets) . ')';
        $insert_stmt = $sqlite->prepare($sql);
        foreach($years as $year){
            echo "======= Processing year $year =======\n";
            $days = daysOfYear::compute($year);
            foreach($days as $day){
                $datetime = $day . ' 12:00:00';
                $coords = Meeus1::ephem($datetime, $tigephPlanets)['planets'];
                $fields = [];
                $fields['day'] = $day;
                foreach($coords as $tigephCode => $coord){
                    $fields[IAA::TIGEPH_IAA[$tigephCode]] = $coord;
                }
                $insert_stmt->execute($fields);
            }
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Execution time $dt s - $dth\n";
        return '';
    }
    
    private static function initalizeSqlite(string $sqlite_path, array $planets): \PDO {
        $sqlite_exists = is_file($sqlite_path);
        $sqlite = new \PDO('sqlite:' . $sqlite_path);
        if(!$sqlite_exists){
            // create table planet(day character(10),SO real,MO real,ME real,VE real,MA real,JU real,SA real,UR real,NE real,PL real,NN real)
            $sql1 = 'create table planet(day character(10),' . implode(' real,', $planets) . ' real)';
            $sql2 = 'create unique index idx_day on planet(day)';
            $sqlite->exec($sql1);
            $sqlite->exec($sql2);
        }
        return $sqlite;
    }

}// end class
