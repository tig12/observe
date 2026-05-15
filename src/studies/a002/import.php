<?php
/******************************************************************************
    
    Transfers the contents of death-fr.sqlite3 to data.sqlite3
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-05-14 16:17:33+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\a002;

use observe\model\Observe;
use observe\app\ICommand;
use observe\app\Params;
use observe\model\IStudy;
use tiglib\time\seconds2HHMMSS;
use tiglib\filesystem\yieldFile;

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
        $outSqliteFile = $study->getSqliteDataPath();
        if(is_file($outSqliteFile)) {
            $answer = Params::answerYN("WARNING: File $outSqliteFile already exists.\nThis operation will delete it permanently.\n");
            if($answer !== true) {
                return '';
            }
        }
        //
        // Prepare
        //
        // data.sqlite3
        $outSqlite = $study->initalizeSqliteData();
        // ex: insert into date(mother,father,child,wedding) values(:mother,:father,:child,:wedding)
        $sql = 'insert into date(' . implode(',', $study->config['dates']) .') values(:' . implode(',:', $study->config['dates']) . ')';
        $stmt_insert = $outSqlite->prepare($sql);
        // note: the 2 pragma don't change execution speed - but begin transaction really does
        $outSqlite->query('pragma synchronous = off');
        $outSqlite->query('pragma journal_mode = memory');
        $outSqlite->query('begin transaction');
        //
        // Execute
        //
        $t1 = microtime(true);
        $outdir = $study->getWorkingDirectory();
        $loop = 0;
        $inFilename = $study->config['raw-file-path'];
        foreach(yieldFile::loop($inFilename) as $line){
            // jnais00;mnais00;anais00;JNAISM;MNAISM;ANAISM;JNAISP;MNAISP;ANAISP;JMAR;MMAR;AMAR;rangmar00;cnaism;cnaisp;d00;j00;dp;jp;dm;jm;dma;jma;id;id2
            if($loop % 100000 == 0){
                echo "$loop\n";
            }
            $loop++;
            if($loop == 1){
                continue; // line containing names of csv fields
            }
            $fields = explode(';', $line);
            $C = $fields[2] . '-' . $fields[1] . '-' . $fields[0]; // child
            $M = $fields[5] . '-' . $fields[4] . '-' . $fields[3]; // mother
            $F = $fields[8] . '-' . $fields[7] . '-' . $fields[6]; // father
            $W = '';
            if($fields[11] != '0000'){
                $W = $fields[11] . '-' . $fields[10] . '-' . $fields[9]; // wedding
            }
            $stmt_insert->execute([
                ':mother'  => $M,
                ':father'  => $F,
                ':child'   => $C,
                ':wedding' => $W,
            ]);
        }
        $outSqlite->query('end transaction');
        echo "Inserted $loop lines in database $outSqliteFile\n";
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Execution time $dt s - $dth\n";
        return '';
    }
    
} // end class
