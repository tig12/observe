<?php
/******************************************************************************
    Computes planetary positions for a date range.
    
    Parameters defined in commands/prepare.yml, in section "planets-sqlite"
    
    Stores the results in var/tmp/planets.sqlite3
    
    @license    GPL
    @history    2026-02-13 20:51:50+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands;

use observe\app\Command;
use observe\app\Observe;
use observe\parts\astro\ephem;
use observe\parts\astro\time;
use observe\app\ObserveException;
use tigeph\ephem\meeus1\Meeus1;
use tigeph\model\IAA;

class prepareAstro implements Command {
        
    public static function execute($params=[]){
        //
        // Check parameters
        //
        $msg = "Usage: php run-observe.php prepare planets <date or date range>\n"
            . "Ex: php run-observe.php prepare planets 1970\n"
            . "    php run-observe.php prepare planets 1970-1985\n";
        // optional parameter to specify dates to compute
        if(empty($params[Observe::PARAM_OPTIONAL_STRING])){
            echo "MISSING PARAMETER: you must specify date range\n$msg";
            return;
        }
        if(count($params[Observe::PARAM_OPTIONAL_STRING]) > 1){
            $useless = $params[Observe::PARAM_OPTIONAL_STRING][1];
            echo "USELESS PARAMETER: $useless\n$msg";
            return;
        }
        // sqlite path
        if(!isset($params['sqlite-path'])){
            echo "MISSING 'sqlite-path' PARAMETER IN COMMAND FILE\n"
                . "Add this parameter in commands/prepare.yml.\n";
            return;
        }
        // planet codes
        $msg = IAA::checkCodes($params['planets']);
        if($msg != ''){
            echo $msg . "\n";
            return;
        }
        $planets = $params['planets']; // = ['SO', 'MO', 'ME', 'VE', 'MA', 'JU', 'SDA', 'UR', 'NE', 'PL', 'NN']
        //
        // compute $years
        //
        $years = time::yearRange($params[Observe::PARAM_OPTIONAL_STRING][0]);
        //
        // Initialize sqlite database
        //
        $sqlite = self::initalizeSqlite($params['sqlite-path'], $planets);
        //
        // compute planet positions and store in sqlite
        //
        $tigephPlanets = ephem::iaa2tigeph($planets);
        // insert into planets(day,SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN) values(:day,:SO,:MO,:ME,:VE,:MA,:JU,:SA,:UR,:NE,:PL,:NN)
        $sql = 'insert into planets(day,' . implode(',', $planets) .') values(:day,:' . implode(',:', $planets) . ')';
        $insert_stmt = $sqlite->prepare($sql);
        foreach($years as $year){
            echo "======= Processing year $year =======\n";
            $days = time::listDays($year);
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
    }
    
    public static function initalizeSqlite(string $sqlite_path, array $planets): \PDO {
        $sqlite_exists = is_file($sqlite_path);
        $sqlite = new \PDO('sqlite:' . $sqlite_path);
        if(!$sqlite_exists){
            // create table planets(day character(10),SO real,MO real,ME real,VE real,MA real,JU real,SA real,UR real,NE real,PL real,NN real)
            $sql1 = 'create table planets(day character(10),' . implode(' real,', $planets) . ' real)';
            $sql2 = 'create unique index idx_bday ON planets(day)';
            $sqlite->exec($sql1);
            $sqlite->exec($sql2);
        }
        return $sqlite;
    }

}// end class
