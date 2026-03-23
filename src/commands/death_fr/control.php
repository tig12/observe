<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:48:18+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\death_fr;

use observe\model\ICommand;
use observe\model\Studies;
use observe\model\SqlitePlanets;
use observe\model\distrib\Distribs;
use observe\model\distrib\AddDistribs;
use tiglib\filesystem\mkdir;
use tiglib\time\seconds2HHMMSS;
use tiglib\math\modN;

class control implements ICommand {
    
    /** In sqlite death-fr, table person **/
    private static int $maxRowid;
    
    private static \PDOStatement $stmt_one_person;
    
    /** 
        Called by Studies::runCommand()
    **/
    public static function execute(array $studyConfig, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe death-fr control <controls>\n"
            . "<controls> can be a number (ex: \"2\") or a range (ex: \"2-4\")\n"
            . "Examples of use:\n"
            . "    php run-observe death-fr control 5          # build control-005\n"
            . "    php run-observe death-fr control 5-10       # build control-005 ... control-010\n"
            ;
        if(count($params) == 0){
            return "MISSING PARAMETER control.\n$usage";
        }
        if(count($params) != 1){
            return "INVALID CALL.\n$usage";
        }
        $p_one = '/^\d+$/';
        $p_range = '/^\d+-\d+$/';
        $controls = [];
        preg_match($p_one, $params[0], $m);
        if(count($m) == 1){
            $controls[] = $m[0];
        }
        else {
            preg_match($p_range, $params[0], $m);
            if(count($m) == 1){
                [$from, $to] = explode('-', $m[0]);
                $controls = range($from, $to); // if $to > $from, range() returns years from $to to $from
            }
            else {
                return "INVALID PARAMETER: \"{$params[0]}\"\n$usage";
            }
        }
        //
        // Prepare
        //
        $outDir = Studies::getControlsDirectory($studyConfig); // ex: var/studies/death-fr/controls
        //
        // sqlite database containing temporary data
        $sqlite_tmp = Death_Fr::getTmpSqlite();
        //
        // sqlite database containing data coming from data.gouv.fr
        $sqlite_persons = Death_Fr::getPersonSqlite();
        $stmt = $sqlite_persons->query('select max(rowid) from person');
        self::$maxRowid = $stmt->fetch(\PDO::FETCH_ASSOC)['max(rowid)']; // = select count(*) from person
        self::$stmt_one_person = $sqlite_persons->prepare('select bday,dday from person where rowid=:rowid');
        // order by rowid : to respect age at death distribution, see comment of otherPerson()
        $stmt_many_persons = $sqlite_persons->prepare("select rowid,bday from person order by rowid limit :limit offset :offset");
        $LIMIT = 1000;
        //
        // Execute
        //
        // The person database is processed by small packets of size $LIMIT
        // At the end of each iteration, the distributions are stored in tmp database
        // This permits to stop and restart execution without re-computing from the beginning of person database
        $t1 = microtime(true);
        foreach($controls as $control){
            $controlName = 'control-' . str_pad($control, 3, '0', STR_PAD_LEFT);
            echo "======================== Generating $controlName ==================================\n";
            self::prepareTmpSqlite($sqlite_tmp, $controlName);
            $controlDir = $outDir . DS . $controlName; // ex: var/studies/death-fr/controls/control-003
            $testFiles = glob($controlDir . DS . '*');
            if(count($testFiles) != 0){
                // If a control is generated through multiple executions, the intermediate results are stored in tmp sqlite db.
                // The final results are written on disk only when the computation is complete.
                // So if the directory is not empty, it means that the computation was already done.
                $answer = readline("WARNING: Directory $controlDir is not empty.\n"
                        . "This operation will override its content. Are you sure (y/n)? ");
                if(strtolower($answer) != 'y') {
                    if(strtolower($answer) != 'n') {
                        echo "WRONG ANSWER - respond with 'y' or 'n'. Nothing was modified\n";
                    }
                    else {
                        echo "OK, nothing was modified\n";
                    }
                    return '';
                }
            }
            mkdir::execute($controlDir, 0755, true);
            // ex: $distribs = ['birth' => 'aspects => ['SO-SO=>[0 ... 359], ...], 'death' => [...], 'birth-death' => [...]]
            [$distribs, $OFFSET] = self::getLastDistribsAndOffsetFromTmpSqlite($sqlite_tmp, $controlName);
            while($OFFSET < self::$maxRowid){
                echo ($OFFSET / 1000) . " k\n";
                //
                // function passed to computeDistributions()
                //
                $f = function() use ($stmt_many_persons, $OFFSET, $LIMIT) {
                    $stmt_many_persons->execute([':offset' => $OFFSET, ':limit' => $LIMIT]);
                    foreach($stmt_many_persons->fetchAll(\PDO::FETCH_ASSOC) as $person){
                        yield array_values(self::otherPerson($person));
                    }
                };
                $newDistribs = Distribs::computeDistributions($f, $studyConfig);
                $distribs = AddDistribs::add($distribs, $newDistribs, $studyConfig);
                self::storeDistribsAndOffsetInTmpSqlite($sqlite_tmp, $controlName, $OFFSET, $distribs);
                unset($newDistribs);
                $OFFSET += $LIMIT;
            } // end while($OFFSET < self::$maxRowid)
            
            Distribs::storeDistributions($controlDir, $distribs, $studyConfig);
            
        } // end loop on controls
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Execution time $dt s - $dth\n";
        return '';
    }
    
    /**
        Randomly selects another person.
        => IMPORTANT CODE - the method to choose another person is arbitrary and must be verified.
        Current method selects $other not very far from $person to try to respect death age distribution.
        
        $nTry and $interval were introduced to avoid a risk of infinite loop
        (if all $other in the interval are dead before $person is born).
        
        @return Associative array containing 2 keys: "bday" and "dday"
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
    // Database created in init.php
    //

    /**
        Inserts a line in tmp sqlite database for a given control.
        Insertion is done only if no line corresponding to this control exists.
    **/
    public static function prepareTmpSqlite(\PDO $sqlite_tmp, string $controlName): void {
        $stmt = $sqlite_tmp->query("select count(*) from control where slug='$controlName'");
        $value = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($value['count(*)'] == 0){
            $stmt = $sqlite_tmp->query("insert into control(slug) values('$controlName')");
            echo "Prepared tmp database for $controlName\n";
        }
    }
    
    public static function getLastDistribsAndOffsetFromTmpSqlite(\PDO $sqlite_tmp, string $controlName): array {
        $stmt = $sqlite_tmp->query("select distribs,last_offset from control where slug='$controlName'");
        $value = $stmt->fetch(\PDO::FETCH_ASSOC);
        return [ json_decode($value['distribs'], true), $value['last_offset'] ];
    }

    public static function storeDistribsAndOffsetInTmpSqlite(\PDO $sqlite_tmp, string $controlName, int $offset, array &$distrib): void {
        $json = json_encode($distrib);
        $stmt = $sqlite_tmp->query("update control set distribs='$json', last_offset=$offset where slug='$controlName' limit 1");
    }

} // end class
