<?php
/******************************************************************************
    
    Builds distributions of control groups
    
    @license    GPL
    @history    2026-02-24 14:25:00+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use observe\app\Command;
use observe\shared\astro\sqlitePlanets;
use observe\shared\astro\aspects as aspectUtils;
use observe\shared\distrib\degrees as degreeUtils;
use observe\shared\distrib\csvDistrib;
use observe\shared\fileSystem;

class control implements Command {
    
    private static \PDOStatement $stmt_one_person;
    
    /** In table person **/
    private static int $maxRowid;
    
    public static function execute($params=[]){
        //
        // Parameter check
        //
        if(!isset($params['out-subdir'])){
            echo "Missing parameter 'out-subdir' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        $outDir = DeathFr::$WORKING_DIR . DS . $params['out-subdir'];
        if(!is_dir($outDir)){
            // Not created to avoid mistakes
            echo "Directory $outDir does not exist. Create it before executing this command\n";
            return;
        }
        //
        if(!isset($params['n-controls'])){
            echo "Missing parameter 'n-controls' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        } else if(!is_int($params['n-controls'])){
            echo "Parameter 'n-controls' must be an integer in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        //
        if(!isset($params['n-start'])){
            echo "Missing parameter 'n-start' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        } else if(!is_int($params['n-start'])){
            echo "Parameter 'n-start' must be an integer in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        //
        // Prepare
        //
        // planet codes
        $allPlanets = DeathFr::$PLANETS;
        // sqlite database containing the planet positions
        $sqlite_planets = sqlitePlanets::getSqlite();
        // select SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planets where day=:day
        $stmt_planets = $sqlite_planets->prepare('select ' . implode(',', $allPlanets) . ' from planets where day=:day');
        // sqlite database containing data coming from data.gouv.fr
        $sqlite_persons = DeathFr::getSqlite();
        $stmt = $sqlite_persons->query('select max(rowid) from person');
        self::$maxRowid = $stmt->fetch(\PDO::FETCH_ASSOC)['max(rowid)']; // = select count(*) from person
        self::$stmt_one_person = $sqlite_persons->prepare('select bday,dday from person where rowid=:rowid');
        $limit = 1000;
        $offset = 0;
        $stmt_many_persons = $sqlite_persons->prepare("select rowid,bday from person order by rowid limit :limit offset :offset");
        //
        // Execute
        //
        $t1 = microtime(true);
        for($i=$params['n-start']; $i < $params['n-controls'] + $params['n-start']; $i++){
            $controlDir = str_pad($i, 3, '0', STR_PAD_LEFT);
//            fileSystem::mkdir($outDir . DS . $controlDir);
            $distrib = degreeUtils::emptyDoubleDistrib($allPlanets, $allPlanets);
            while($offset < self::$maxRowid){
                $stmt_many_persons->execute([':offset' => $offset, ':limit' => $limit]);
                $planets_birth = [];
                $planets_death = [];
                foreach($stmt_many_persons->fetchAll(\PDO::FETCH_ASSOC) as $person){
                    $other = self::otherPerson($person);
// echo "\n"; print_r($person); echo "\n";
// print_r($other);
exit;
                    $bday = $person['bday'];
                    $dday = $other['dday'];
                    
                }
            }
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        echo "(execution time $dt s)\n";
    }
    
    /**
        Randomly selects another person
        => IMPORTANT CODE - the method to choose another person must be verified.
        Current method selects $other not very far from $person to respect death age distribution.
        $nTry and $interval were introduced to avoid a risk of infinite loop
        (if all $other in the interval are dead before $person is born).
    **/
    private static function otherPerson(array $person): array {
        $other = [];
        $nTry = 0;
        $interval = 100;
        while(true){
            if($nTry > 2 * $interval){
                $interval *= 2;
            }
            $rand = rand(-$interval, $interval);
            if($rand == 0){
                continue; // don't take the same person
            }
            $nTry++;
            $newRowid = ($person['rowid'] + $rand) % self::$maxRowid;
            self::$stmt_one_person->execute([':rowid' => $newRowid]);
            $other = self::$stmt_one_person->fetch(\PDO::FETCH_ASSOC);
echo 'other = ' . print_r($other); echo "\n"; exit;
            if($other['dday'] < $person['bday']){
                continue; // incoherent
            }
            break;
        }
        return $other;
    }
    
} // end class
