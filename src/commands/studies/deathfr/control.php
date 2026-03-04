<?php
/******************************************************************************
    
    Builds distributions of control groups
    
    @license    GPL
    @history    2026-02-24 14:25:00+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use observe\shared\astro\sqlitePlanets;
use observe\shared\astro\aspects as aspectUtils;
use observe\shared\distrib\degrees as degreeUtils;
use observe\shared\distrib\addDistrib;
use observe\shared\distrib\csvDistrib;
use observe\shared\fileSystem;
use tiglib\patterns\command\Command;
use tiglib\math\modN;
use tiglib\misc\smallDump;
use tiglib\time\seconds2HHMMSS;

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
        $allPlanets = DeathFr::$PLANETS; // planet codes
        //
        // sqlite database containing the planet positions
        $sqlite_planets = sqlitePlanets::getSqlite();
        // select SO,MO,ME,VE,MA,JU,SA,UR,NE,PL,NN from planets where day=:day
        $stmt_planets = $sqlite_planets->prepare('select ' . implode(',', $allPlanets) . ' from planets where day=:day');
        //
        // sqlite database containing temporary data
        $sqlite_tmp = DeathFr::getTmpSqlite();
        //
        // sqlite database containing data coming from data.gouv.fr
        $sqlite_persons = DeathFr::getPersonSqlite();
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
        for($i=$params['n-start']; $i < $params['n-controls'] + $params['n-start']; $i++){
            $controlName = 'control-' . str_pad($i, 3, '0', STR_PAD_LEFT);
            $controlDir = $outDir . DS . $controlName;
            echo "======================== Start generating $controlName ==================================\n";
            self::prepareTmpSqlite($sqlite_tmp, $controlName);
            fileSystem::mkdir($controlDir);
            $distrib = self::getLastDistribFromTmpSqlite($sqlite_tmp, $controlName); // ['SO-SO=>[0 ... 359], ...]
            $OFFSET = self::getLastOffsetFromTmpSqlite($sqlite_tmp, $controlName);
            while($OFFSET < self::$maxRowid){
                echo ($OFFSET / 1000) . ' k     ';
                $stmt_many_persons->execute([':offset' => $OFFSET, ':limit' => $LIMIT]);
                $planets_birth = []; // [0=>['SO' => 125.54, 'MO' => 241.451, ...], ...]
                $planets_death = [];
                
                foreach($stmt_many_persons->fetchAll(\PDO::FETCH_ASSOC) as $person){
                    $other = self::otherPerson($person);
                    $stmt_planets->execute([':day' => $person['bday']]);
                    $birth_planets = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
                    if($birth_planets === false){
                        echo 'PERSON ERROR: ' . smallDump::print_r($person, true) . "\n";
                        continue;
                    }
                    $planets_birth[] = $birth_planets;
                    $stmt_planets->execute([':day' => $other['dday']]);
                    $death_planets = $stmt_planets->fetch(\PDO::FETCH_ASSOC);
                    if($death_planets === false){
                        echo 'PERSON ERROR: ' . smallDump::print_r($person, true) . "\n";
                        continue;
                    }
                    $planets_death[] = $death_planets;
                } // end foreach($stmt_many_persons->fetchAll))
                
                // intermediate computations to flush memory (specially $planets_birth and $planets_death)
                $aspects = aspectUtils::computeDouble($planets_birth, $planets_death, $allPlanets, $allPlanets);
                $newDistrib = degreeUtils::computeDistrib($aspects);
                $distrib = addDistrib::compute($distrib, $newDistrib);
                self::storeDistribAndOffsetInTmpSqlite($sqlite_tmp, $controlName, $OFFSET, $distrib);
                unset($aspects);
                unset($newDistrib);
                $planets_birth = [];
                $planets_death = [];
                //
                $OFFSET += $LIMIT;
            } // end while($OFFSET < self::$maxRowid)
            
            // Store result
            foreach($distrib as $key => $values){
                $outFile = $controlDir . DS . $key . '.csv';
                $contents = csvDistrib::distrib2csv($values);
                fileSystem::saveFile($outFile, $contents);
            }
        } // end loop on controls
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Execution time $dt s - $dth\n";
    }
/* 
control-001: execution time 25370.55 s - 7.04 h
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
            if($other === false){
                echo "OTHER PERSON = false: rowid = $newRowid\n";
                continue;
            }
            if($other['dday'] < $person['bday']){
                continue; // incoherent
            }
            break;
        }
        return $other;
    }
    
    //
    // tmp sqlite management
    // Added to permit to stop and resume execution
    //

    /** Inserts a line in tmp sqlite database for a given control **/
    public static function prepareTmpSqlite(\PDO $sqlite_tmp, string $controlName): void {
        $stmt = $sqlite_tmp->query("select count(*) from control where slug='$controlName'");
        $value = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($value['count(*)'] == 0){
            $stmt = $sqlite_tmp->query("insert into control(slug) values('$controlName')");
            echo "Prepared tmp database for $controlName\n";
        }
    }
    
    public static function getLastOffsetFromTmpSqlite(\PDO $sqlite_tmp, string $controlName): int {
        $stmt = $sqlite_tmp->query("select last_offset from control where slug='$controlName'");
        $value = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $value['last_offset'];
    }

    public static function getLastDistribFromTmpSqlite(\PDO $sqlite_tmp, string $controlName): array {
        $res = [];
        $stmt = $sqlite_tmp->query("select distrib from control where slug='$controlName'");
        $value = $stmt->fetch(\PDO::FETCH_ASSOC);
        return json_decode($value['distrib'], true);
    }

    public static function storeDistribAndOffsetInTmpSqlite(\PDO $sqlite_tmp, string $controlName, int $offset, array &$distrib): void {
        $json = json_encode($distrib);
        $stmt = $sqlite_tmp->query("update control set distrib='$json', last_offset=$offset where slug='$controlName' limit 1");
    }

} // end class
