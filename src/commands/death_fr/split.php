<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:47:41+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\death_fr;

use observe\model\ICommand;
use tiglib\time\diff;
use tiglib\time\seconds2HHMMSS;

class split implements ICommand {
    
    /** 
        Arbitrary values used to build the splits, in days.
        The splits in this study concern the age at death.
        keys = limits of the interval, in days
        values = corresponding names, used to build file names
    **/
    const array SPLITS = [
        'all' => [
            '0'           => '0',
            '54787.5'     => '150years',
        ],
        'age' => [
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
    
    /**
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
        $split_limits = self::SPLITS[$split];
        $keys = array_keys($split_limits);
        $values = array_values($split_limits);
        $nSplits = count($split_limits) - 1;
        $nValues = array_fill(0, $nSplits, 0); // nb of values stored in each split - useful only for command output
        $froms = [];
        $tos = [];
        $filenames = [];
        $bz2s = [];
        // sqlite database containing data coming from data.gouv.fr
        $sqlite_persons = Death_Fr::getPersonSqlite();
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
        return '';
    }
    
    /** 
        Builds one split containing all persons
    **/
    private static function split_all(\PDO $sqlite_persons, string $outDir): void {
        $stmt = $sqlite_persons->query('select max(rowid) from person');
        $MAXROWID = $stmt->fetch(\PDO::FETCH_ASSOC)['max(rowid)']; // = select count(*) from person
        $OFFSET = 0;
        $LIMIT = 1000;
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
