<?php
/******************************************************************************
    Contains informations shared by several commands of this package.    

    @license    GPL
    @history    2026-02-21 18:17:03+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\death_fr;

use observe\model\IStudy;
use observe\app\ObserveException;

class Death_fr implements IStudy {
    
    private static string $SQLITE_PERSON_PATH;
    private static string $SQLITE_TMP_PATH;
    
    /**
    **/
    public static function init(array &$studyConfig): void {
        if(!isset($studyConfig['sqlite-death-fr'])){
            throw new ObserveException("Missing entry 'sqlite-death-fr' in file {$studyConfig['study-file']}");
        }
        self::$SQLITE_PERSON_PATH = $studyConfig['sqlite-death-fr'];
        //
        if(!isset($studyConfig['sqlite-tmp'])){
            throw new ObserveException("Missing entry 'sqlite-tmp' in file {$studyConfig['study-file']}");
        }
        self::$SQLITE_TMP_PATH = $studyConfig['sqlite-tmp'];
    }
    
    /** Returns a PDO link to death-fr.sqlite3 **/
    public static function getPersonSqlite(): \PDO {
        if(!is_file(self::$SQLITE_PERSON_PATH)){
            throw new ObserveException('Sqlite database ' . self::$SQLITE_PERSON_PATH . " does not exist\n"
                . "You first need to create it (using g5 program)\n");
        }
        return new \PDO('sqlite:' . self::$SQLITE_PERSON_PATH);
    }
    
    /** Returns a PDO link to death-fr.sqlite3 **/
    public static function getTmpSqlite(): \PDO {
        if(!is_file(self::$SQLITE_TMP_PATH)){
            throw new ObserveException('Sqlite database ' . self::$SQLITE_TMP_PATH . " does not exist\n"
                . "You first need to create it with this command:\nphp run-observe.php death-fr init\n");
        }
        return new \PDO('sqlite:' . self::$SQLITE_TMP_PATH);
    }
    
} // end class
                                                                                                                               