<?php
/******************************************************************************

    Generates downloadable compressed csv files in output/studies/<study>/csv
    Called by commands/output.php
    
    Example of call: php run-observe.php death-fr output csv dim2

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-05-01 12:08:38+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\output;

use observe\model\IStudy;
use tiglib\filesystem\mkdir;
use tiglib\compress\zip;

class output_csv {
    
    const array POSSIBLE_WHAT = [
        'all'   => 'Generates all downloadable distributions',
        'dim1'  => 'Generates dim1 downloadable distributions',
        'dim2'  => 'Generates dim2 downloadable distributions',
    ];

    /**
        Called by output::execute()
        
        @param  $params are parameters passed to command output, so $params[0] = 'csv'.
        @return Error message or empty string if ok.
    **/
    public static function execute(IStudy $study, array $params): string {
        if(!in_array($params[1], array_keys(self::POSSIBLE_WHAT))){
            return "INVALID PARAMETER object: \"{$params[1]}\".";
        }
        $what = $params[1];
        
        switch($what){
            case 'dim1': self::generateDim1($study); break;
            case 'dim2': self::generateDim2($study); break;
            case 'all':
                self::generateDim1($study);
                self::generateDim2($study);
            break;
        }
        return '';
    }
    
    private static function generateDim1(IStudy $study): void {
        $baseOutDir = $study->getOutputCsvDirectory();
        $baseInDir_obs = $study->getObservedDirectory();
        $baseInDir_exp = $study->getExpectedDirectory();
        //
        // distrib1
        //
        foreach($study->config['dates'] as $dateName){    // ex: $dateName = birth
            echo "Generating dim1 zip files for $dateName\n";
            //
            // Days and years
            //
            $inDir_obs = implode(DS, [$baseInDir_obs, $dateName]);
            $inDir_exp = implode(DS, [$baseInDir_exp, $dateName]);
            $outDir_obs = implode(DS, [$baseOutDir, 'observed', $dateName]);
            $outDir_exp = implode(DS, [$baseOutDir, 'expected', $dateName]);
            mkdir::execute($outDir_obs);
            mkdir::execute($outDir_exp);
            $inFile_obs = $inDir_obs . DS . 'day.csv';
            $outFile_obs = $outDir_obs . DS . 'day.csv.zip';
            zip::zipFile($inFile_obs, $outFile_obs);
            $inFile_obs = $inDir_obs . DS . 'year.csv';
            $outFile_obs = $outDir_obs . DS . 'year.csv.zip';
            zip::zipFile($inFile_obs, $outFile_obs);
            $inFile_exp = $inDir_exp . DS . 'day.csv';
            $outFile_exp = $outDir_exp . DS . 'day.csv.zip';
            zip::zipFile($inFile_exp, $outFile_exp);
            $inFile_exp = $inDir_exp . DS . 'year.csv';
            $outFile_exp = $outDir_exp . DS . 'year.csv.zip';
            zip::zipFile($inFile_exp, $outFile_exp);
            //
            // Planet positions
            //
            $inDir_obs = implode(DS, [$baseInDir_obs, $dateName, 'positions']);
            $inDir_exp = implode(DS, [$baseInDir_exp, $dateName, 'positions']);
            $outDir_obs = implode(DS, [$baseOutDir, 'observed', $dateName, 'positions']);
            $outDir_exp = implode(DS, [$baseOutDir, 'expected', $dateName, 'positions']);
            mkdir::execute($outDir_obs);
            mkdir::execute($outDir_exp);
            $inFiles_obs = glob($inDir_obs . DS . '*.csv');
            foreach($inFiles_obs as $inFile_obs){
                $outFile_obs = $outDir_obs . DS . basename($inFile_obs) . '.zip';
                zip::zipFile($inFile_obs, $outFile_obs);
            }
            $inFiles_exp = glob($inDir_exp . DS . '*.csv');
            foreach($inFiles_exp as $inFile_exp){
                $outFile_exp = $outDir_exp . DS . basename($inFile_exp) . '.zip';
                zip::zipFile($inFile_exp, $outFile_exp);
            }
            //
            // Aspects
            //
            $inDir_obs = implode(DS, [$baseInDir_obs, $dateName, 'aspects', 'dim1']);
            $inDir_exp = implode(DS, [$baseInDir_exp, $dateName, 'aspects', 'dim1']);
            $outDir_obs = implode(DS, [$baseOutDir, 'observed', $dateName, 'aspects', 'dim1']);
            $outDir_exp = implode(DS, [$baseOutDir, 'expected', $dateName, 'aspects', 'dim1']);
            mkdir::execute($outDir_obs);
            mkdir::execute($outDir_exp);
            $inFiles_obs = glob($inDir_obs . DS . '*.csv');
            foreach($inFiles_obs as $inFile_obs){
                $outFile_obs = $outDir_obs . DS . basename($inFile_obs) . '.zip';
                zip::zipFile($inFile_obs, $outFile_obs);
            }
            $inFiles_exp = glob($inDir_exp . DS . '*.csv');
            foreach($inFiles_exp as $inFile_exp){
                $outFile_exp = $outDir_exp . DS . basename($inFile_exp) . '.zip';
                zip::zipFile($inFile_exp, $outFile_exp);
            }
        }
        //
        // distrib2
        //
        for($i=0; $i < count($study->config['dates']); $i++){
            for($j=$i+1; $j < count($study->config['dates']); $j++){
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j]; // ex: birth-death
                echo "Generating dim1 zip files for $dateName\n";
                //
                // Age
                //
                $inDir_obs = implode(DS, [$baseInDir_obs, $dateName, 'age', 'dim1']);
                $inDir_exp = implode(DS, [$baseInDir_exp, $dateName, 'age', 'dim1']);
                $outDir_obs = implode(DS, [$baseOutDir, 'observed', $dateName, 'age', 'dim1']);
                $outDir_exp = implode(DS, [$baseOutDir, 'expected', $dateName, 'age', 'dim1']);
                mkdir::execute($outDir_obs);
                mkdir::execute($outDir_exp);
                $inFile_obs = $inDir_obs . DS . 'age-Y.csv';
                $outFile_obs = $outDir_obs . DS . 'age-Y.csv.zip';
                zip::zipFile($inFile_obs, $outFile_obs);
                $inFile_obs = $inDir_obs . DS . 'age-M.csv';
                $outFile_obs = $outDir_obs . DS . 'age-M.csv.zip';
                zip::zipFile($inFile_obs, $outFile_obs);
                $inFile_exp = $inDir_exp . DS . 'age-Y.csv';
                $outFile_exp = $outDir_exp . DS . 'age-Y.csv.zip';
                zip::zipFile($inFile_exp, $outFile_exp);
                $inFile_exp = $inDir_exp . DS . 'age-M.csv';
                $outFile_exp = $outDir_exp . DS . 'age-M.csv.zip';
                zip::zipFile($inFile_exp, $outFile_exp);
                //
                // Interaspects
                //
                $inDir_obs = implode(DS, [$baseInDir_obs, $dateName, 'interaspects', 'dim1']);
                $inDir_exp = implode(DS, [$baseInDir_exp, $dateName, 'interaspects', 'dim1']);
                $outDir_obs = implode(DS, [$baseOutDir, 'observed', $dateName, 'interaspects', 'dim1']);
                $outDir_exp = implode(DS, [$baseOutDir, 'expected', $dateName, 'interaspects', 'dim1']);
                mkdir::execute($outDir_obs);
                mkdir::execute($outDir_exp);
                $inFiles_obs = glob($inDir_obs . DS . '*.csv');
                foreach($inFiles_obs as $inFile_obs){
                    $outFile_obs = $outDir_obs . DS . basename($inFile_obs) . '.zip';
                    zip::zipFile($inFile_obs, $outFile_obs);
                }
                $inFiles_exp = glob($inDir_exp . DS . '*.csv');
                foreach($inFiles_exp as $inFile_exp){
                    $outFile_exp = $outDir_exp . DS . basename($inFile_exp) . '.zip';
                    zip::zipFile($inFile_exp, $outFile_exp);
                }
            }
        }
    }
    
    /* 
        NOTE: written for obs and exp - but files generated only for obs
        because control groups only handle dim1
        Will be useful when control group are also computed for dim2
    */
    private static function generateDim2(IStudy $study): void {
        $baseOutDir = $study->getOutputCsvDirectory();
        $baseInDir_obs = $study->getObservedDirectory();
        $baseInDir_exp = $study->getExpectedDirectory();
        //
        // distrib1
        //
        foreach($study->config['dates'] as $dateName){    // ex: $dateName = birth
            echo "Generating dim2 zip files for $dateName\n";
            // Planet positions not computed for dim2
            //
            // Aspects
            //
            $inDir_obs = implode(DS, [$baseInDir_obs, $dateName, 'aspects', 'dim2']);
            $inDir_exp = implode(DS, [$baseInDir_exp, $dateName, 'aspects', 'dim2']);
            $outDir_obs = implode(DS, [$baseOutDir, 'observed', $dateName, 'aspects', 'dim2']);
            $outDir_exp = implode(DS, [$baseOutDir, 'expected', $dateName, 'aspects', 'dim2']);
            mkdir::execute($outDir_obs);
            mkdir::execute($outDir_exp);
            $inFiles_obs = glob($inDir_obs . DS . '*.csv');
            foreach($inFiles_obs as $inFile_obs){
                $outFile_obs = $outDir_obs . DS . basename($inFile_obs) . '.zip';
                zip::zipFile($inFile_obs, $outFile_obs);
            }
            $inFiles_exp = glob($inDir_exp . DS . '*.csv');
            foreach($inFiles_exp as $inFile_exp){
                $outFile_exp = $outDir_exp . DS . basename($inFile_exp) . '.zip';
                zip::zipFile($inFile_exp, $outFile_exp);
            }
        }
        //
        // distrib2
        //
        for($i=0; $i < count($study->config['dates']); $i++){
            for($j=$i+1; $j < count($study->config['dates']); $j++){
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j]; // ex: birth-death
                echo "Generating dim2 zip files for $dateName\n";
                // Age not computed for dim2
                //
                // Interaspects
                //
                $inDir_obs = implode(DS, [$baseInDir_obs, $dateName, 'interaspects', 'dim2']);
                $inDir_exp = implode(DS, [$baseInDir_exp, $dateName, 'interaspects', 'dim2']);
                $outDir_obs = implode(DS, [$baseOutDir, 'observed', $dateName, 'interaspects', 'dim2']);
                $outDir_exp = implode(DS, [$baseOutDir, 'expected', $dateName, 'interaspects', 'dim2']);
                mkdir::execute($outDir_obs);
                mkdir::execute($outDir_exp);
                $inFiles_obs = glob($inDir_obs . DS . '*.csv');
                foreach($inFiles_obs as $inFile_obs){
                    $outFile_obs = $outDir_obs . DS . basename($inFile_obs) . '.zip';
                    zip::zipFile($inFile_obs, $outFile_obs);
                }
                $inFiles_exp = glob($inDir_exp . DS . '*.csv');
                foreach($inFiles_exp as $inFile_exp){
                    $outFile_exp = $outDir_exp . DS . basename($inFile_exp) . '.zip';
                    zip::zipFile($inFile_exp, $outFile_exp);
                }
            }
        }
    }
    
    
} // end class
