<?php
/******************************************************************************
    
    Builds Castille distributions, as described in http://cura.free.fr/xx/18cas3fr.html
    These distributions can be seen as the same nature of observed distributions.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-04-13 23:14:10+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\death_fr;

use observe\model\Observe;
use observe\model\ICommand;
use observe\model\Studies;
use tiglib\time\diff;
use tiglib\time\seconds2HHMMSS;
use tiglib\filesystem\mkdir;

class castille implements ICommand {
    
    /** 
        Called by Studies::runCommand()
    **/
    public static function execute(array &$studyConfig, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe death-fr castille <split>\n"
            . "<split> can be:\n  - " . implode("\n  - ", $studyConfig['splits']) . "\n";
        if(count($params) != 1){
            return "MISSING PARAMETER split.\n$usage";
        }
        $split = $params[0];
        if(!in_array($split, $studyConfig['splits'])){
            return "INVALID PARAMETER split: \"$split\".\n$usage";
        }
        //
        // Prepare
        //
        // sqlite database containing data coming from data.gouv.fr
        $sqlite_persons = Death_fr::getPersonSqlite();
        $stmt_many_persons = $sqlite_persons->prepare("select rowid,bday,dday from person order by rowid limit :limit offset :offset");
        $LIMIT = $studyConfig['split-limit']; // squat another command...
        $stmt = $sqlite_persons->query('select max(rowid) from person');
        $MAXROWID = $stmt->fetch(\PDO::FETCH_ASSOC)['max(rowid)']; // = select count(*) from person
        // $baseOutdir = directory of the split, containing the sub-directories of each subgroup
        $baseOutdir = Studies::getSplitDirectory($studyConfig, $split);
        //
        // Execute
        //
        $t1 = microtime(true);
        $splitDirnames = Death_fr::getSplitSubgroups($split);
        for($i=0; $i < $nSubgroups; $i++){
            $froms[$i] = $keys[$i];
            $tos[$i] = $keys[$i + 1];
            // $subdirName = directory specific to one subgroup of the split.
            $subdir = $baseOutdir . DS . $splitDirnames[$i];
            mkdir::execute($subdir, 0755, true);
            $filenames[$i] = $subdir . DS . 'data.csv.bz2';
            $bz2s[$i] = bzopen($filenames[$i], 'w');
        }
        //
        // Main loop
        //
        $OFFSET = 0;
        while($OFFSET < $MAXROWID){
            echo ($OFFSET / 1000) . " k\n";
            $stmt_many_persons->execute([':offset' => $OFFSET, ':limit' => $LIMIT]);
            foreach($stmt_many_persons->fetchAll(\PDO::FETCH_ASSOC) as $person){
                $diff = diff::compute(new \Datetime($person['bday']), new \Datetime($person['dday']), 'D', 2);
                // find the split corresponding to $diff
                for($i=0; $i < $nSubgroups; $i++){
                    if($diff >= $froms[$i] && $diff < $tos[$i]){
                        bzwrite($bz2s[$i], $person['bday'] . Observe::CSV_SEP . $person['dday'] . "\n");
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
        for($i=0; $i < $nSubgroups; $i++){
            bzclose($bz2s[$i]);
            echo "Generated {$nValues[$i]} lines in {$filenames[$i]}\n";
        }
        echo "Total generated lines: " . array_sum($nValues) . "\n";
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Execution time $dt s - $dth\n";
        return '';
    }
    
} // end class
