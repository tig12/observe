<?php
/******************************************************************************
    
    @license    GPL
    @history    2026-02-17 00:44:29+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use tiglib\patterns\command\Command;
use tiglib\time\diff;
use tiglib\time\seconds2HHMMSS;

class split implements Command {
    
    /** 
        Arbitrary values used to build the splits, in days
        keys = limits of the interval, in days
        values = corresponding names, used to build file names
    **/
    const array SPLITS = [
        'all' = [
            '0'           => '0',
            '54787.5'     => '150years',
        ],
        'age' = [
            '0'           => '0',
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
    
    public static function execute($params=[]){
        //
        // Parameter check
        //
        if(!isset($params['split'])){
            echo "Missing parameter 'split' in command file commands/death-fr/death-fr.yml\n";
            echo "Possible values:\n  - " . implode("\n  - ", DeathFr::$POSSIBLE_SPLITS) . "\n";
            return;
        }
        $split = $params['split'];
        if(($msg = DeathFr::checkParam_split($split)) !== true){
            echo $msg;
            return;
        }
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
        // Prepare
        //
        $split_limits = self::SPLITS($split);
        $keys = array_keys($split_limits);
        $values = array_values($split_limits);
        $nSplits = count($split_limits) - 1;
        $nValues = array_fill(0, $nSplits, 0); // nb of values stored in each split - useful only for command output
        $froms = [];
        $tos = [];
        $filenames = [];
        $bz2s = [];
        // sqlite database containing data coming from data.gouv.fr
        $sqlite_persons = DeathFr::getPersonSqlite();
        $stmt_persons = $sqlite_persons->prepare("select rowid,bday from person order by rowid limit :limit offset :offset");
        $LIMIT = 1000;
        //
        // Execute
        //
        $t1 = microtime(true);
        // Note: obliged to open the bz2s of all splits because we don't know in which split a line of the database will go.
        // Possible to change the algo : treat each split one by one, but would oblige to loop over the whole database for each split.
        for($i=0; $i < $nSplits; $i++){
            $froms[$i] = $keys[$i];
            $tos[$i] = $keys[$i + 1];
            $filenames[$i] = $outDir . DS . sprintf("%02d", $i + 1) . '--' . $values[$i] . '-' . $values[$i+1] . '.csv.bz2';
            $bz2s[$i] = bzopen($filenames[$i], 'w');
        }
        //
        // Execute
        //
        $OFFSET = 0;
        while($OFFSET < $MAXROWID){
        
            $stmt_many_persons->execute([':offset' => $OFFSET, ':limit' => $LIMIT]);
            $stmt_persons->execute();
            foreach($stmt_persons->fetchAll(\PDO::FETCH_ASSOC) as $person){
                $diff = diff::compute(new \Datetime($person['bday']), new \Datetime($person['dday']), 'D', 2);
                // find the split corresponding to $diff
                for($i=0; $i < $nSplits; $i++){
                    if($diff >= $froms[$i] && $diff < $tos[$i]){
                        bzwrite($bz2s[$i], $person['bday'] . ';' . $person['dday'] . "\n");
                        $nValues[$i]++;
                        break;
                    }
                }
            }
            $OFFSET += $LIMIT;
        } // end while($OFFSET < $MAXROWID)
            
        //
        // Store result
        //
        for($i=0; $i < $nSplits; $i++){
            bzclose($bz2s[$i]);
            echo "Generated {$nValues[$i]} lines in {$filenames[$i]}\n";
        }
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Execution time $dt s - $dth\n";
        
    }
    
    /** 
        Builds one split containing all persons
    **/
    private static function split_all(\PDO $sqlite_persons, string $outDir): void {
        $stmt = $sqlite_persons->query('select max(rowid) from person');
        $MAXROWID = $stmt->fetch(\PDO::FETCH_ASSOC)['max(rowid)']; // = select count(*) from person
        $OFFSET = 0;
        $LIMIT = 100000;
        $stmt_persons = $sqlite_persons->prepare("select bday,dday from person order by rowid limit :limit offset :offset");
        
        $outFile = $outDir . DS . 'all.csv.bz2';
        $bz2 = bzopen($outFile, 'w');
        while($OFFSET < $MAXROWID){
continue;
            $stmt_persons->execute([':offset' => $OFFSET, ':limit' => $LIMIT]);
            foreach($stmt_persons->fetchAll(\PDO::FETCH_ASSOC) as $person){
                bzwrite($bz2, $person['bday'] . ';' . $person['dday'] . "\n");
            }
            $OFFSET += $LIMIT;
        }
        bzclose($bz);
        //
        // Store result
        //
        echo "Generated $outFile\n";
    }

} // end class
                                                                                                                               