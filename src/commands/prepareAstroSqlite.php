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

class prepareAstroSqlite implements Command {
        
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
        // tmp dir
        if(!isset($params['tmp-dir'])){
            echo "MISSING 'tmp-dir' PARAMETER IN COMMAND FILE\n"
                . "Add this parameter in commands/prepare.yml.\n";
            return;
        }
        $dir = $params['tmp-dir'];
        if(!is_dir($dir)){
            echo "ERROR directory '$dir' does not exist\n"
                . "Create this directory before executing this command.\n";
            return;
        }
        // sqlite path
        if(!isset($params['sqlite-file'])){
            echo "MISSING 'path-sqlite' PARAMETER IN COMMAND FILE\n"
                . "Add this parameter in commands/prepare.yml.\n";
            return;
        }
        // TODO check planets
        //$planets = ['SO', 'MO', 'ME', 'VE', 'MA', 'JU', 'SA', 'UR', 'NE', 'PL', 'NN'];
        $planets = $params['planets'];
        //
        // compute $years
        //
        $years = time::yearRange($params[Observe::PARAM_OPTIONAL_STRING][0]);
        //
        // Initialize sqlite database
        //
        $sqlite_path = $params['tmp-dir'] . DS . $params['path-sqlite'];
        self::initalizeSqlite($sqlite_path);
        
        //
        // compute planet positions
        //
        $tigephPlanets = ephem::iaa2tigeph($planets);
        //
        foreach($years as $year){
            $file = $dir . DS . $year . '.csv';
            $contents = $file_header;
            $days = time::listDays($year);
            foreach($days as $day){
                $datetime = $day . ' 12:00:00';
                // note: $coords is an associative array with keys expressed with constants of SolarSystemC
                // but $planets and $tigephPlanets share the same order, so it's no use to convert back to IAA keys
                $coords = Meeus1::ephem($datetime, $tigephPlanets)['planets'];
                $contents .= $day . Observe::CSV_SEP . implode(Observe::CSV_SEP, $coords) . "\n";
            }
            file_put_contents($file, $contents);
            echo "Generated $file\n";
        }
    }
    
    /**
        @param  $
    **/
    public static function initalizeSqlite(string $sqlite_path): void {
        if(!is_file($sqlite_path)){
        }

    }

}// end class
