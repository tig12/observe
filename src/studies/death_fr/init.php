<?php
/********************************************************************************
    
    Initializes the local sqlite database containing temporary data
    filled during execution of very long commands
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @history    2026-02-26 09:09:06+01:00, Thierry Graff : creation
********************************************************************************/
namespace observe\studies\death_fr;

use observe\shared\distrib\degrees as degreeUtils;
use observe\model\ICommand;

class init implements ICommand {
    
    /** 
        @param $params empty array
        @return Empty string, echoes its output
    **/
    public static function execute(array $studyConfig, array $params): string {
        $path_sqlite = DeathFr::$SQLITE_TMP_PATH;
        if(is_file($path_sqlite)) {
            $answer = readline("WARNING: File $path_sqlite already exists.\n         This operation will delete it permanently. Are you sure (y/n)? ");
            if(strtolower($answer) != 'y') {
                if(strtolower($answer) != 'n') {
                    echo "WRONG ANSWER - respond with 'y' or 'n'. Nothing was modified\n";
                }
                else {
                    echo "OK, nothing was modified\n";
                }
                return;
            }
            unlink($path_sqlite);
            echo "Deleted file $path_sqlite\n";
        }
        
        $dir = dirname($path_sqlite);
        if(!is_dir($dir)) {
            mkdir($dir, 0777, true);
            echo "Created directory $dir\n";
        }
        
        $distrib = degreeUtils::emptyDoubleDistrib(DeathFr::$PLANETS, DeathFr::$PLANETS);
        $json = json_encode($distrib);
        $sql = <<<SQL
create table control(
    slug varchar(255) unique,
    last_offset int default 0,
    distrib text default '$json'
)
SQL;
        $sqlite = new \PDO('sqlite:' . $path_sqlite);
        $sqlite->exec($sql);
        echo "Initialized local sqlite database $path_sqlite\n";
    }
    
}// end class    
