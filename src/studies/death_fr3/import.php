<?php
/******************************************************************************
    
    Transfers the contents of death-fr.sqlite3 to data.csv.bz2.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-03-11 17:47:41+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\death_fr3;

use observe\model\Observe;
use observe\app\ICommand;
use observe\app\Params;
use observe\model\IStudy;
use tiglib\time\seconds2HHMMSS;
use tiglib\filesystem\mkdir;

class import implements ICommand {
    
    /** 
        Called by Run::runCommand()
        @param $params empty array
    **/
    public static function execute(IStudy $study, array $params): string {
        //
        // Parameter check
        //
        if(count($params) != 0){
            return "INVALID PARAMETER: \"{$params[0]}\". This command must be called without parameter\n";
        }
        //
        $outFilename = $study->getDatafile();
        if(is_file($outFilename)) {
            $answer = Params::answerYN("WARNING: File $outFilename already exists.\nThis operation will delete it permanently.\n");
            if($answer !== true) {
                return '';
            }
        }
        //
        // Prepare
        //
        // sqlite database containing data coming from data.gouv.fr
        $sqlite_persons = Death_fr::getPersonSqlite();
        $stmt_many_persons = $sqlite_persons->prepare("select rowid,bday,dday from person order by rowid limit :limit offset :offset");
        $LIMIT = $study->config['import-limit'];
        $stmt = $sqlite_persons->query('select max(rowid) from person'); // = select count(*)
        $MAXROWID = $stmt->fetch(\PDO::FETCH_ASSOC)['max(rowid)']; // = select count(*) from person
        // $baseOutdir = directory of the split, containing the sub-directories of each subgroup
        $outdir = $study->getWorkingDirectory();
        //
        // Execute
        //
        $t1 = microtime(true);
        $bz2 = bzopen($outFilename, 'w');
        //
        // Main loop
        //
        $OFFSET = 0;
        $nWritten = 0; // for output only
        while($OFFSET < $MAXROWID){
            $stmt_many_persons->execute([':offset' => $OFFSET, ':limit' => $LIMIT]);
            foreach($stmt_many_persons->fetchAll(\PDO::FETCH_ASSOC) as $person){
                bzwrite($bz2, $person['bday'] . Observe::CSV_SEP . $person['dday'] . "\n");
                $nWritten++;
            }
            $OFFSET += $LIMIT;
            if($nWritten % 100000 == 0){
                echo ($nWritten / 1000) . " k\n";
            }
        }
        //
        // Store result
        //
        bzclose($bz2);
        echo "Generated $nWritten lines in $outFilename\n";
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Execution time $dt s - $dth\n";
        return '';
    }
    
} // end class
