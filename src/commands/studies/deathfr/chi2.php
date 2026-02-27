<?php
/******************************************************************************
    
    Computes chi2 test.
    
    obs = observed
    exp = expected
    
    @license    GPL
    @history    2026-02-26 22:40:03+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\studies\deathfr;

use observe\app\Command;
use observe\shared\distrib\csvDistrib;
use observe\shared\fileSystem;
use tiglib\stats\chi2 as chi2Utils;

class chi2 implements Command {
    
    public static function execute($params=[]){
        //
        // Parameter check
        //
        if(!isset($params['split'])){
            echo "Missing parameter 'split' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            echo "Possible values:\n  - " . implode("\n  - ", DeathFr::$POSSIBLE_SPLITS) . "\n";
            return;
        }
        $split = $params['split'];
        if(($msg = DeathFr::checkParam_split($split)) !== true){
            echo $msg;
            return;
        }
        //
        if(!isset($params['in-expected-subdir'])){
            echo "Missing parameter 'in-expected-subdir' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        $inDir_exp = DeathFr::$WORKING_DIR . DS . $params['in-expected-subdir'];
        if(!is_dir($inDir_exp)){
            echo "ERROR: Directory $inDir_exp does not exist. Enter a correct value in " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        //
        if(!isset($params['in-observed-subdir'])){
            echo "Missing parameter 'in-observed-subdir' in command file " . DeathFr::$COMMAND_FILE_PATH . "\n";
            return;
        }
        $inDir_obs = DeathFr::$WORKING_DIR . DS . $params['in-observed-subdir'];
        if(!is_dir($inDir_obs)){
            echo "ERROR: Directory $inDir_obs does not exist. Enter a correct value in " . DeathFr::$COMMAND_FILE_PATH . "\n";
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
        $allKeys = []; //  ['SO-SO', 'SO-MO', ...]
        foreach(DeathFr::$PLANETS as $code1){
            foreach(DeathFr::$PLANETS as $code2){
                $allKeys[] = "$code1-$code2";
            }
        }
        // $inDir_obs contains one subdirectory per split, named for ex '01--0-2months', '02--2months-6months', ... by control::execute()
        // This structure is used to get the split names
        $inSubdirs_obs = glob($inDir_obs . DS . '*');
        if(count($inSubdirs_obs) == 0){
            echo "Directory $inDir_obs is empty - Generate first the distributions before executing this command\n";
            return;
        }
        // $distribs_exp = ['SO-SO' => [0 => 1544, 1 => 21648, ...], ...]
        $distribs_exp = [];
        $files_exp = glob($inDir_exp . DS . '*.csv');
        foreach($files_exp as $file_exp){
            $key = str_replace('.csv', '', basename($file_exp)); // ex: 'SO-SO'
            $distribs_exp[$key] = csvDistrib::csv2distrib($file_exp, false);
        }
        $OUT_CSV_HEADER = "KEY;CHI2;P\n";
        //
        // Execute
        // build $res = ['SO-SO' => ['chi2' => 3.5, 'p' => 0.12], ...]
        // one $res per split
        //
        foreach($inSubdirs_obs as $inSubdir_obs){
            $res = [];
            //$splitName = substr(basename($inSubdir_obs), 4);
            $splitName = basename($inSubdir_obs);
//echo "splitName = $splitName\n";
            $distribFiles_obs = glob($inSubdir_obs . DS . '*.csv');
            foreach($distribFiles_obs as $distribFile_obs){
//echo "distribFile_obs = $distribFile_obs\n";
                $key = str_replace('.csv', '', basename($distribFile_obs)); // ex: 'SO-SO'
//echo "$key\n";
                $distrib_obs = csvDistrib::csv2distrib($distribFile_obs, false);
//echo "\n"; print_r($distrib_obs); echo "\n";
                $chi2 = chi2Utils::chi2($distrib_obs, $distribs_exp[$key]);
                $res[$key] = [
                    'chi2'  => $chi2,
                    'p'     => chi2Utils::chi2Proba($chi2, 359),
                ];
//echo "\n"; print_r($res[$key]); echo "\n";
//exit;
            } // end loop on $distribFiles_obs
            $csvContents = $OUT_CSV_HEADER;
            foreach($res as $key => $value){
                $csvContents .= $key . ';' . $value['chi2'] . ';' . $value['p'] . "\n";
            }
            $outFile = $outDir . DS . $splitName . '.csv';
//echo "$outFile\n";
            fileSystem::saveFile($outFile, $csvContents);
//exit;
        } // end loop on $inSubdir_obs
    }
    
} // end class
