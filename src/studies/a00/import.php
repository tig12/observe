<?php
/******************************************************************************
    
    Transfers the contents of a00.csv to data.csv.bz2.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-05-05 22:40:16+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\a00;

use observe\model\Observe;
use observe\app\ICommand;
use observe\model\IStudy;
use observe\model\Studies;
use tiglib\time\seconds2HHMMSS;
use tiglib\filesystem\mkdir;

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
        // Execute
        //
        $t1 = microtime(true);
        $inFilename = $study->config['raw-file-path'];
        $outFilename = $study->getDatafile();
        $bz2 = bzopen($outFilename, 'w');
        $loop = 0;
        foreach(yieldFile::loop($inFilename) as $line){
            // jnais00;mnais00;anais00;JNAISM;MNAISM;ANAISM;JNAISP;MNAISP;ANAISP;JMAR;MMAR;AMAR;rangmar00;cnaism;cnaisp;d00;j00;dp;jp;dm;jm;dma;jma;id;id2
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
            $outLine = implode(Observe::CSV_SEP, [$M, $F, $C, $W]);
            bzwrite($bz2, $outLine . "\n");
        }
        //
        // Store result
        //
        bzclose($bz2);
        echo "Generated $loop lines in $outFilename\n";
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Execution time $dt s - $dth\n";
        return '';
    }
    
} // end class
