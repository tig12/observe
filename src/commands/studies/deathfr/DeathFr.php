<?php
/******************************************************************************
    Contains informations shared by several commands of this package.    

    @license    GPL
    @history    2026-02-21 18:17:03+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use observe\app\CommandFile;
use observe\app\Run;
use observe\app\ObserveException;

DeathFr::init();

class DeathFr {
    
    /** Identifier of command file commands/death-fr/death-fr.yml **/
    const string COMMAND_FILE_STRING = 'death-fr/death-fr';

    /** Identifier of command file commands/death-fr/death-fr.yml **/
    public static string $COMMAND_FILE_PATH;

    /** Contains all intermediate files of this study **/
    public static string $WORKING_DIR;
    
    /**  Array of splits handled in this study **/
    public static array $POSSIBLE_SPLITS;
    
    /** Path to the sqlite database containing the data coming fro data.gouv.fr **/
    public static string $SQLITE_PATH;
    
    /**  list of planets involved in this study **/
    public static array $PLANETS;
    
    public static function init(): void {
        
        $commandFile = new CommandFile(self::COMMAND_FILE_STRING);
        self::$COMMAND_FILE_PATH = Run::command2File(self::COMMAND_FILE_STRING);
        $data = $commandFile->getData();
        
        if(!isset($data['variables']['working-dir'])){
            throw new ObserveException("Missing key variables.working-dir in file " . self::$COMMAND_FILE_PATH);
        }
        self::$WORKING_DIR = $data['variables']['working-dir'];
        
        if(!isset($data['variables']['possible-splits'])){
            throw new ObserveException("Missing key variables.possible-splits in file " . self::$COMMAND_FILE_PATH);
        }
        self::$POSSIBLE_SPLITS = $data['variables']['possible-splits'];
        
        if(!isset($data['variables']['sqlite-death-fr'])){
            throw new ObserveException("Missing key variables.sqlite-death-fr in file " . self::$COMMAND_FILE_PATH);
        }
        self::$SQLITE_PATH = $data['variables']['sqlite-death-fr'];
        
        if(!isset($data['variables']['planets'])){
            throw new ObserveException("Missing key variables.planets in file " . self::$COMMAND_FILE_PATH);
        }
        self::$PLANETS = $data['variables']['planets'];
    }
    
    /** Returns a PDO link to death-fr.sqlite3 **/
    public static function getSqlite(): \PDO {
        if(!is_file(self::$SQLITE_PATH)){
            throw new ObserveException('Sqlite database ' . self::$SQLITE_PATH . "does not exist\n"
                . "You first need to create it (using g5 program)\n");
        }
        return new \PDO('sqlite:' . self::$SQLITE_PATH);
    }
    
    /**
        If $split is valid (belongs to possible-splits if deat-fr.yml), returns true.
        Otherwise returns an error message.
    **/
    public static function checkParam_split(string $split): string|true {
        if(!in_array($split, self::$POSSIBLE_SPLITS)){
            $msg = "Invalid value for parameter 'split': '$split',  in command file " . DeathFr::$COMMAND_FILE_PATH . "\n"
                 . "Possible values:\n  - " . implode("\n  - ", DeathFr::$POSSIBLE_SPLITS) . "\n";
            return $msg;
        }
        return true;
    }
    
} // end class
                                                                                                                               