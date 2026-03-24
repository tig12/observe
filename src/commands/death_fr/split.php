<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:47:41+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\death_fr;

use observe\model\Observe;
use observe\model\ICommand;
use observe\model\Studies;
use tiglib\time\diff;
use tiglib\time\seconds2HHMMSS;
use tiglib\filesystem\mkdir;

class split implements ICommand {
    
    /** 
        Called by Studies::runCommand()
    **/
    public static function execute(array $studyConfig, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe death-fr split <split>\n"
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
        $split_limits = Death_fr::SPLITS[$split];
        $keys = array_keys($split_limits);
        $values = array_values($split_limits);
        $nSubgroups = count($split_limits) - 1;
        $nValues = array_fill(0, $nSubgroups, 0); // nb of values stored in each subgroup - useful only for command output
        $froms = [];
        $tos = [];
        $filenames = [];
        $bz2s = [];
        // sqlite database containing data coming from data.gouv.fr
        $sqlite_persons = Death_fr::getPersonSqlite();
        $stmt_many_persons = $sqlite_persons->prepare("select rowid,bday,dday from person order by rowid limit :limit offset :offset");
        $LIMIT = 1000;
        $stmt = $sqlite_persons->query('select max(rowid) from person');
        $MAXROWID = $stmt->fetch(\PDO::FETCH_ASSOC)['max(rowid)']; // = select count(*) from person
        // $baseOutdir = directory of the split, containing the sub-directories of each subgroup
        $baseOutdir = Studies::getSplitDirectory($studyConfig, $split);
        //
        // Execute
        //
        $t1 = microtime(true);
        // Note: obliged to open the bz2s of all splits because we don't know in which subgroup a line of the database will go.
        // Possible to change the algo : treat each subgroup one by one, but would oblige to loop over the whole database for each subgroup.
        $splitDirnames = Death_fr::getSplitDirnames($split);
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
