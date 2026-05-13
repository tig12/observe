<?php
/******************************************************************************
    Contains informations shared by several commands of this package.    

    @license    GPL
    @copyright  Thierry Graff
    
    @history    2026-05-12 18:25:50+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\death_fr3;

use observe\model\Study;
use observe\model\IStudy;
use observe\model\Studies;
use observe\app\ObserveException;

class Death_fr3 extends Study implements IStudy {
    
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
    // Code that should go in Study.php if data.sqlite3 replaces data.csv.bz2
    //
    
    /** Returns a PDO link to death-fr.sqlite3 **/
    public static function getPersonSqlite(): \PDO {
        if(!is_file(self::$SQLITE_PERSON_PATH)){
            throw new ObserveException('Sqlite database ' . self::$SQLITE_PERSON_PATH . " does not exist\n"
                . "You first need to create it (using g5 program)\n");
        }
        return new \PDO('sqlite:' . self::$SQLITE_PERSON_PATH);
    }
    
} // end class
                                                                                                                               