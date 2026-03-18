<?php
/******************************************************************************
    Auxiliary code to access the sqlite database containing pre-computed planet positions.
    
    @license    GPL
    @history    2026-02-21 22:29:01+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\model;

use observe\app\Config;
use observe\app\ObserveException;

class SqlitePlanets {
    
    /** Returns a PDO link to planets.sqlite3 **/
    public static function getSqlite(): \PDO {
        
        if(!is_file(Config::$data['one-day-ephemeris']['sqlite-planets'])){
            throw new ObserveException('Sqlite database ' . Config::$data['one-day-ephemeris']['sqlite-planets'] . "does not exist\n"
                . "You first need to create it using php run-observe.php prepare planets <date range>\n");
        }
        return new \PDO('sqlite:' . Config::$data['one-day-ephemeris']['sqlite-planets']);
    }
    
}// end class
