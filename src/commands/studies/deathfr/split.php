<?php
/******************************************************************************
    
    @license    GPL
    @history    2026-02-17 00:44:29+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use observe\app\Command;
use tiglib\time\diff;

class split implements Command {
    
    /** Name of the file README generated in output directory **/
    const string README_FILENAME = 'README';
    
    /** Contents of the file README generated in output directory **/
    const string README_CONTENTS = <<<README
The files in this directory contains csv files.
Each line of these files contains birth day and death day, format YYYY-MM-DD.
README;
    
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
        $msg = DeathFr::checkParam_split($split);
        if($msg !== true){
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
            echo "Directory $outDir does not exist. Create it before executing this command\n";
            return;
        }
        // sqlite database containing data coming from data.gouv.fr
        $sqlite_persons = DeathFr::getSqlite();
        //
        // Execute
        //
        $t1 = microtime(true);
        switch($split){
        	case 'all':        self::split_all($sqlite_persons, $outDir); break;
        	case 'death-age':  self::split_age($sqlite_persons, $outDir); break;
        }
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        echo "(execution time $dt s)\n";
        
    }
    
    /** 
        Builds one split containing all persons
    **/
    private static function split_all(\PDO $sqlite_persons, string $outDir): void {
        $outFile = $outDir . DS . 'all.csv.bz2';
        $bz2 = bzopen($outFile, 'w');
        $stmt_persons = $sqlite_persons->prepare("select bday,dday from person");
        $stmt_persons->execute();
        foreach($stmt_persons->fetchAll(\PDO::FETCH_ASSOC) as $person){
            bzwrite($bz2, $person['bday'] . ';' . $person['dday'] . "\n");
        }
        //
        // Store result
        //
        echo "Generated $outFile\n";
        file_put_contents($outDir . DS . self::README_FILENAME, self::README_CONTENTS);
    }

    /** 
        Build several splits, separated by age at death
    **/
    private static function split_age(\PDO $sqlite_persons, string $outDir): void {
        //
        // Prepare
        //
        // Arbitrary values used to build the splits, in days
        // keys = limits of the interval, in days
        // values = corresponding names, used to build file names
        $limits = [
            '0'           => '0',
            '60'          => '2months',
            '182.625'     => '6months',
            '365.25'      => '1year',
            '730.5'       => '2years',
            '1826.25'     => '5years',
            '3652.5'      => '10years',
            '7305'        => '20years',
            '18262.5'     => '50years',
            '54787.5'     => '150years',
        ];
        $keys = array_keys($limits);
        $values = array_values($limits);
        $nSplits = count($limits) - 1;
        $nValues = array_fill(0, $nSplits, 0); // nb of values stored in each split - just for command output
        $froms = [];
        $tos = [];
        $filenames = [];
        $bz2s = [];
        for($i=0; $i < $nSplits; $i++){
            $froms[$i] = $keys[$i];
            $tos[$i] = $keys[$i + 1];
            $filenames[$i] = $outDir . DS . sprintf("%02d", $i + 1) . '--' . $values[$i] . '-' . $values[$i+1] . '.csv.bz2';
            $bz2s[$i] = bzopen($filenames[$i], 'w');
        }
        //
        // Execute
        //
        $stmt_persons = $sqlite_persons->prepare("select bday,dday from person");
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
        //
        // Store result
        //
        for($i=0; $i < $nSplits; $i++){
            bzclose($bz2s[$i]);
            echo "Generated {$nValues[$i]} lines in {$filenames[$i]}\n";
        }
        file_put_contents($outDir . DS . self::README_FILENAME, self::README_CONTENTS);
    }

    
} // end class
                                                                                                                               