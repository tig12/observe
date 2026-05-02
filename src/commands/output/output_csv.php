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
            case 'dim2': self::generateDim2($study); break;
            case 'all':
                self::generateDim2($study);
            break;
        }
        return '';
    }
    
    private static function generateDim2(IStudy $study): void {
        $baseOutDir = $study->getOutputCsvDirectory();
        //
        // Aspects
        //
        foreach($study->config['dates'] as $dateName){    // ex: $dateName = birth
            echo "Generating zip files for $dateName\n";
            $inDir = implode(DS, [$study->getObservedDirectory(), $dateName, 'aspects', 'dim2']);
            $outDir = implode(DS, [$baseOutDir, $dateName, 'aspects', 'dim2']);
            mkdir::execute($outDir);
            $inFiles = glob($inDir . DS . '*.csv');
            foreach($inFiles as $inFile){
                $outFile = $outDir . DS . basename($inFile) . '.zip';
                zip::zipFile($inFile, $outFile);
            }
        }
        //
        // Interaspects
        //
        for($i=0; $i < count($study->config['dates']); $i++){
            for($j=$i+1; $j < count($study->config['dates']); $j++){
                $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j]; // ex: birth-death
                echo "Generating zip files for $dateName\n";
                $inDir = implode(DS, [$study->getObservedDirectory(), $dateName, 'interaspects', 'dim2']);
                $outDir = implode(DS, [$baseOutDir, $dateName, 'interaspects', 'dim2']);
                mkdir::execute($outDir);
                $inFiles = glob($inDir . DS . '*.csv');
                foreach($inFiles as $inFile){
                    $outFile = $outDir . DS . basename($inFile) . '.zip';
                    zip::zipFile($inFile, $outFile);
                }
            }
        }
    }
    
    
} // end class
