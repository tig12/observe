<?php
/******************************************************************************
    Auxiliary code to access the sqlite database containing pre-computed planet positions.
    Used by studies with untimed dates.
    
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
    
    // ***********************************************************************************
    // Query coordinates of planets for one day
    // ***********************************************************************************
    
    private static ?\PDOStatement $stmt_planets_1day = null;
    
    /**
        Function to call before using self::getPlanets_1day()
        @param  $sqlite_planets     A \PDO obtained by self::getSqlite();
        @param  $codes              Array of planet codes
    **/
    public static function init_1day(\PDO $sqlite_planets, array $codes): void {
        $planets = implode(',', $codes);
        // select SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planet where day = :d
        self::$stmt_planets_1day = $sqlite_planets->prepare("select $planets from planet where day = :d");
    }
    
    /** 
        Returns the planet positions for 2 days
        select SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planet where day = :d
        @param  $sqlite_planets     A \PDO obtained by self::getSqlite();
        @param  $day1 and $day2     format YYYY-MM-DD
        @return Associative array containing the planet coordinates for $day
                [
                    0 => ['SO' => 123.654, ... 'MO' => 321.654],
                    1 => ['SO' => 23.774, ... 'MO' => 54.874],
                ]
    **/
    public static function getPlanets_1day(\PDO $sqlite_planets, string $day): array {
        self::$stmt_planets_1day->execute([ 'd' => $day ]);
        return self::$stmt_planets_1day->fetch(\PDO::FETCH_ASSOC);
    }

    // ***********************************************************************************
    // Query coordinates of planets for two days
    // ***********************************************************************************
    
    private static ?\PDOStatement $stmt_planets_2days = null;
    
    /**
        Function to call before using self::getPlanets_2days()
        @param  $sqlite_planets     A \PDO obtained by self::getSqlite();
        @param  $codes              Array of planet codes
    **/
    public static function init_2days(\PDO $sqlite_planets, array $codes): void {
        $planets = implode(',', $codes);
        // select SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planet where day in(:d0,:d1)
        self::$stmt_planets_2days = $sqlite_planets->prepare("select $planets from planet where day in(:d0,:d1)");
    }
    
    /** 
        Returns the planet positions for 2 days
        select SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planet where day in(:d0,:d1)
        @param  $sqlite_planets     A \PDO obtained by self::getSqlite();
        @param  $day1 and $day2     format YYYY-MM-DD
        @return array of 2 elements containing the planets for $day1 and $day2
                [
                    0 => ['SO' => 123.654, ... 'MO' => 321.654],
                    1 => ['SO' => 23.774, ... 'MO' => 54.874],
                ]
    **/
    public static function getPlanets_2days(\PDO $sqlite_planets, string $day1, string $day2): array {
        self::$stmt_planets_2days->execute([ 'd0' => $day1, 'd1' => $day2 ]);
        $planets = self::$stmt_planets_2days->fetchAll(\PDO::FETCH_ASSOC);
        if(count($planets) == 1){
            // particular case: day1 = day2
            $planets[1] = $planets[0];
        }
        return $planets;
    }

}// end class
