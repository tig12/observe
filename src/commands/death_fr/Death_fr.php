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
    
    /** 
        Arbitrary values used to build the splits, in days.
        The splits in this study concern the age at death.
        keys = limits of the interval, in days
        values = corresponding names, used to build file names
    **/
    const array SPLITS = [
        'full' => [
            '0'           => '0',
            '54787.5'     => '150years',
        ],
        'age' => [
            '0'           => '0',
            '2'           => '2days',
            '60'          => '2months',
            '182.625'     => '6months',
            '730.5'       => '2years',
            '1826.25'     => '5years',
            '7305'        => '20years',
            '18262.5'     => '50years',
            '32872.5'     => '90years',
            '54787.5'     => '150years',
        ],
    ];
    
    private static string $SQLITE_PERSON_PATH;
    
    private static string $SQLITE_TMP_PATH;
    
    /** Implementation of IStudy **/
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
    
    /**
        Returns the names of the directories of each subgroup of a split.
        Implementation of IStudy.
    **/
    public static function getSplitDirnames(string $split): array {
        $split_limits = self::SPLITS[$split];
        $values = array_values($split_limits);
        $nSubgroups = count($split_limits) - 1;
        $res = [];
        for($i=0; $i < $nSubgroups; $i++){
            // directory specific to one subgroup of the split.
            $res[] = sprintf("%02d", $i + 1) . '--' . $values[$i] . '-' . $values[$i+1]; // ex: 02--2days-2months
        }
        return $res;
    }
    
    /** Returns a PDO link to death-fr.sqlite3 **/
    public static function getPersonSqlite(): \PDO {
        if(!is_file(self::$SQLITE_PERSON_PATH)){
            throw new ObserveException('Sqlite database ' . self::$SQLITE_PERSON_PATH . " does not exist\n"
                . "You first need to create it (using g5 program)\n");
        }
        return new \PDO('sqlite:' . self::$SQLITE_PERSON_PATH);
    }
    
    /** Returns a PDO link to tmp.sqlite3 **/
    public static function getTmpSqlite(): \PDO {
        if(!is_file(self::$SQLITE_TMP_PATH)){
            throw new ObserveException('Sqlite database ' . self::$SQLITE_TMP_PATH . " does not exist\n"
                . "You first need to create it with this command:\nphp run-observe.php death-fr init\n");
        }
        return new \PDO('sqlite:' . self::$SQLITE_TMP_PATH);
    }
    
} // end class
                                                                                                                               