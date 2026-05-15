<?php
/******************************************************************************
    Contains informations shared by several commands of this package.    

    @license    GPL
    @copyright  Thierry Graff
    
    @history    2026-05-12 18:25:50+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\a002;

use observe\model\Study;
use observe\model\IStudy;
use observe\model\Studies;
use observe\app\ObserveException;

class A002 extends Study implements IStudy {
    
    private static string $SQLITE_PERSON_PATH;
    
    private static string $SQLITE_TMP_PATH;
    
    //
    // Implementation of IStudy
    //
    
    public function __construct(string $studySlug) {
        
        parent::__construct($studySlug);
        
        if(!isset($this->config['raw-file-path'])){
            throw new ObserveException("Missing entry 'raw-file-path' in file {$this->config['__study-file__']}");
        }
        if(!is_file($this->config['raw-file-path'])){
            throw new ObserveException(
                "Unexisting file: {$this->config['raw-file-path']}\n"
                . "Check entry 'raw-file-path' in file {$this->config['__study-file__']}"
            );
        }
    }
    
    public function init($params = []): string {
        return ''; // do nothing
    }
    public function import($params = []): string {
        return import::execute($this, $params);
    }
    public function observed($params = []): string {
        return observed::execute($this, $params);
    }
    public function control($params = []): string {
        return control::execute($this, $params);
    }
    public function expected($params = []): string {
        return expected::execute($this, $params);
    }
    public function stats($params = []): string {
        return stats::execute($this, $params);
    }
    public function dim2($params = []): string {
        return dim2::execute($this, $params);
    }
    public function output($params = []): string {
        return output::execute($this, $params);
    }
    public function dev($params = []): string {
        return dev::execute($this, $params);
    }
    
    /** Returns a PDO link to death-fr.sqlite3 **/
    public static function getPersonSqlite(): \PDO {
        if(!is_file(self::$SQLITE_PERSON_PATH)){
            throw new ObserveException('Sqlite database ' . self::$SQLITE_PERSON_PATH . " does not exist\n"
                . "You first need to create it (using g5 program)\n");
        }
        return new \PDO('sqlite:' . self::$SQLITE_PERSON_PATH);
    }
    
} // end class
                                                                                                                               