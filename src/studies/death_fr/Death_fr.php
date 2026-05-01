<?php
/******************************************************************************
    Contains informations shared by several commands of this package.    

    @license    GPL
    @history    2026-02-21 18:17:03+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\death_fr;

use observe\model\Study;
use observe\model\IStudy;
use observe\model\Studies;
use observe\app\ObserveException;

class Death_fr extends Study implements IStudy {
    
    private static string $SQLITE_PERSON_PATH;
    
    private static string $SQLITE_TMP_PATH;
    
    //
    // Implementation of IStudy
    //
    
    public function __construct(string $studySlug) {
        
        parent::__construct($studySlug);
        
        if(!isset($this->config['sqlite-death-fr'])){
            throw new ObserveException("Missing entry 'sqlite-death-fr' in file {$this->config['__study-file__']}");
        }
        self::$SQLITE_PERSON_PATH = $this->config['sqlite-death-fr'];
        //
        if(!isset($this->config['sqlite-tmp'])){
            throw new ObserveException("Missing entry 'sqlite-tmp' in file {$this->config['__study-file__']}");
        }
        self::$SQLITE_TMP_PATH = $this->config['sqlite-tmp'];
    }
    
    public function init($params = []): string {
        return init::execute($this, $params);
    }
    public function import($params = []): string {
        return import::execute($this, $params);
    }
    public function control($params = []): string {
        return control::execute($this, $params);
    }
    
    //
    // Specific to death-fr
    //
    
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
    
    /**
        Written for phpunit
    **/
    public static function setSqlitePersonPath(string $path): void {
        self::$SQLITE_PERSON_PATH = $path;
    }
    
} // end class
                                                                                                                               