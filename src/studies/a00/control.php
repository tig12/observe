<?php
/******************************************************************************
    
    Compute controls - loads data.csv.bz2 (takes around 228 Mb in memory).
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-05-06 10:27:49+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\a00;

use observe\app\ICommand;
use observe\app\Params;
use observe\model\IStudy;
use observe\model\Observe;
use observe\model\distrib\Distribs;
use tiglib\filesystem\mkdir;
use tiglib\time\seconds2HHMMSS;
use tiglib\math\modN;

class control implements ICommand {
    
    /** Array containing all the dates of data.csv.bz2 **/
    private static array $allRows;
    
    /** Number of dates in data.csv.bz2 **/
    private static int $nRows;
    
    /** 
        Called by Run::runCommand()
    **/
    public static function execute(IStudy $study, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe death-fr control <controls>\n"
            . "<controls> can be a number (ex: \"2\") or a range (ex: \"2-4\")\n"
            . "Examples of use:\n"
            . "    php run-observe death-fr control 5          # builds control-005\n"
            . "    php run-observe death-fr control 5-10       # builds control-005 ... control-010\n"
            ;
        if(count($params) == 0){
            return "MISSING PARAMETER control.\n$usage";
        }
        if(count($params) != 1){
            return "INVALID CALL.\n$usage";
        }
        // control range
        ['controls' => $controls, 'msg' => $msg] = Params::computeControls($params[0]);
        if($msg != ''){
            return "$msg\n$usage";
        }
        //
        // Prepare
        //
        $outDir = $study->getControlsDirectory(); // ex: var/studies/a00/controls
        $inFile = 'compress.bzip2://' . $study->getDatafile();
        self::$allRows = [];
        $fileHandle = fopen($inFile, 'r');
        while(false !== $line = fgets($fileHandle)){
            self::$allRows[] = explode(Observe::CSV_SEP, trim($line));
        }
        fclose($fileHandle);
        self::$nRows = count(self::$allRows);
        //
        // Execute
        //
        foreach($controls as $control){
            $t1 = microtime(true);
            $controlName = 'control-' . str_pad($control, 3, '0', STR_PAD_LEFT);
            echo "======================== Generating $controlName ==================================\n";
            $controlDir = $outDir . DS . $controlName; // ex: var/studies/a00/controls/control-003
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
            mkdir::execute($controlDir);
            //
            // function passed to computeDistributions()
            //
            $f = function(){
                $count = 0;
                foreach(self::$allRows as $row){
                    yield self::otherRow($row);
                    $count++;
                    if($count % 100000 == 0){
                        echo "$count\n";
                    }
                }
            };
            // ex: $distribs = ['birth' => 'aspects => ['SO-SO=>[0 ... 359], ...], 'death' => [...], 'birth-death' => [...]]
            $distribs = Distribs::computeDistributions($f, $study->config['dates'], $study->config['planets']);
            Distribs::storeDistributions($controlDir, $distribs, $study->config['dates']);
            
            $t2 = microtime(true);
            $dt = round($t2 - $t1, 3);
            $dth = seconds2HHMMSS::compute($dt);
            echo "Execution time for $controlName: $dt s - $dth\n";
        } // end loop on controls
        
        return '';
    }
    
    /**
        Randomly builds a fictional {M, F, C, W}.
        This method
            - takes the original mother
            - takes a random father
            - takes a random child
            - if wedding is specified, takes a random wedding
        => IMPORTANT CODE - the method to build another row is arbitrary and must be verified.
        @return regular array containing 4 elements: M, F, C, W
    **/
    private static function otherRow(array $row): array {
        $m = $row[0];
        $f = self::$allRows[rand(0, self::$nRows - 1)][1];
        $c = '0000-00-00';
        while($c < $m || $c < $f){
            $c = self::$allRows[rand(0, self::$nRows - 1)][2];
        }
        if($row[3] != ''){
            $w = '0000-00-00';
            while($w < $m || $c < $w){
                $w = self::$allRows[rand(0, self::$nRows - 1)][3];
            }
        }
        else{
            $w = '';
        }
        return [$m, $f, $c, $w];
    }
    
} // end class
