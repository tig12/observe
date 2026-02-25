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
use observe\shared\distrib\addDistrib;
use observe\shared\distrib\csvDistrib;
use observe\shared\fileSystem;
use tiglib\math\modN;

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
        // order by rowid : to respect age at death distribution, see comment of otherPerson()
        $stmt_many_persons = $sqlite_persons->prepare("select rowid,bday from person order by rowid limit :limit offset :offset");
        //
        // Execute
        //
        $t1 = microtime(true);
        $LIMIT = 1000;
        $nComputed = 0;
        for($i=$params['n-start']; $i < $params['n-controls'] + $params['n-start']; $i++){
            $controlName = str_pad($i, 3, '0', STR_PAD_LEFT);
            $controlDir = $outDir . DS . $controlName;
            echo "======================== Start generating $controlName ==================================\n";
            fileSystem::mkdir($controlDir);
            $distrib = degreeUtils::emptyDoubleDistrib($allPlanets, $allPlanets);
            $OFFSET = 0;
            while($OFFSET < self::$maxRowid){
                echo ($OFFSET / 1000) . " k --- memory = " . (memory_get_usage()/1000) . " k\n";
                $stmt_many_persons->execute([':offset' => $OFFSET, ':limit' => $LIMIT]);
                $planets_birth = [];
                $planets_death = [];
                
                foreach($stmt_many_persons->fetchAll(\PDO::FETCH_ASSOC) as $person){
                    $other = self::otherPerson($person);
                    $stmt_planets->execute([':day' => $person['bday']]);
                    $birth_planets = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
                    $planets_birth[] = $birth_planets;
                    $stmt_planets->execute([':day' => $other['dday']]);
                    $death_planets = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
                    $planets_death[] = $death_planets;
                    $nComputed++;
                } // end foreach($stmt_many_persons->fetchAll))
                
                // intermediate computations to flush memory
                $aspects = aspectUtils::computeDouble($planets_birth, $planets_death, $allPlanets, $allPlanets);
                $newDistrib = degreeUtils::computeDistrib($aspects);
                $distrib = addDistrib::compute($distrib, $newDistrib);
                unset($aspects);
                $planets_birth = [];
                $planets_death = [];
                //
                $OFFSET += $LIMIT;
            } // end while($OFFSET < self::$maxRowid)
            
            // Store result
            foreach($distrib as $key => $values){
                $outFile = $outSubdir . DS . $key . '.csv';
                $contents = csvDistrib::distrib2csv($values);
                fileSystem::saveFile($outFile, $contents);
            }
        } // end loop on controls
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        echo "(execution time $dt s)\n";
    }
/* 
OFFSET = 9205 k --- nComputed = 9205000 --- memory = 3646.504 k
<br>die here /home/thierry/dev/astrostats/observe/src/shared/astro/aspects.php - line 135
*/
    
    /**
        Randomly selects another person.
        => IMPORTANT CODE - the method to choose another person is arbitrary and must be verified.
        Current method selects $other not very far from $person to try to respect death age distribution.
        
        $nTry and $interval were introduced to avoid a risk of infinite loop
        (if all $other in the interval are dead before $person is born).
    **/
    private static function otherPerson(array $person): array {
        $other = [];
        $nTry = 0;
        $interval = 20;
        while(true){
            if($nTry > 2 * $interval){
//echo "======================= CHANGE INTERVAL $interval =======================\n";
                $interval *= 2;
            }
            $rand = rand(-$interval, $interval);
            if($rand == 0){
                continue; // don't take the same person
            }
            $nTry++;
            $newRowid = modN::compute($person['rowid'] + $rand, self::$maxRowid);
            if($newRowid == 0) {
                continue;
            }
            self::$stmt_one_person->execute([':rowid' => $newRowid]);
            $other = self::$stmt_one_person->fetch(\PDO::FETCH_ASSOC);
//echo 'rowid = ' . $person['rowid'] . "  -  newRowid = $newRowid\n";
//echo 'other = ' . self::smallDump($other); echo "\n";
            if($other['dday'] < $person['bday']){
//echo "======================= FOUND INCOHERENT=======================\n";
                continue; // incoherent
            }
            break;
        }
        return $other;
    }
    
    /** Dump a small array on a single line **/
    public static function smallDump(array $array): string {
        return str_replace("\n", ' ', print_r($array, true));
    }
    
} // end class
