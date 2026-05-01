<?php
/******************************************************************************

    Hack to convert the age distributions, from month to year.
    Introduced to correct the distributions without recomputing the control groups.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-04-13 16:50:54+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\dev;

use observe\app\ICommand;
use observe\model\IStudy;
use observe\model\distrib\CsvDistrib;
use tiglib\filesystem\file_put_contents;

class age {
    
    public static function execute(IStudy $study): string {
        $controlDirs = $study->getControlSubdirectories();
        foreach($controlDirs as $controlDir){
            for($i=0; $i < count($study->config['dates']); $i++){
                for($j=$i+1; $j < count($study->config['dates']); $j++){
                    $dateName = $study->config['dates'][$i] . '-' . $study->config['dates'][$j];
                    $inFile = implode(DS, [$controlDir, $dateName, 'age', 'dim1', 'age-M.csv']);
                    $distrib_M = CsvDistrib::csv2distrib_dim1($inFile);
                    $distrib_Y = [];
                    foreach($distrib_M as $m => $value){ // $m = month
                        $y = floor($m / 12);
                        if(!isset($distrib_Y[$y])){
                            $distrib_Y[$y] = 0;
                        }
                        $distrib_Y[$y] += $value;
                    }
                    $csv = CsvDistrib::distrib2csv_dim1($distrib_Y);
                    $outFile = implode(DS, [$controlDir, $dateName, 'age', 'dim1', 'age-Y.csv']);
                    file_put_contents::execute($outFile, $csv);
                }
            }
        }
        return "Done\n";
    }
    
} // end class
