<?php
/******************************************************************************

    Hack to convert the age distributions, from month to year.
    Must be done after all distributions have been computed, and before stats and output.
    Introduced to correct the distributions without recomputing the control groups.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-04-13 16:50:54+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use observe\model\ICommand;
use observe\model\distrib\CsvDistrib;
use tiglib\filesystem\globRecursive;

class correct implements ICommand {
    
    /**
        Called by Studies::runCommand()
    **/
    public static function execute(array &$studyConfig, array $params): string {
return "=== Code descatvated to avoir error === \n" . __FILE__ . "\n";
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe <study> correct\n";
        if(count($params) != 0){
            return "WRONG NUMBER OF ARGUMENTS.\n$usage";
        }
        //                                                                                                                                                      
        // Execution
        //
        $files = globRecursive::compute($studyConfig['working-dir'] . DS . '*age.csv');
        foreach($files as $file){
            $distrib_Y = CsvDistrib::csv2distrib($file);
            $res = [];
            foreach($distrib_Y as $m => $value){ // $m = month
                $y = floor($m / 12);
                if(!isset($res[$y])){
                    $res[$y] = 0;
                }
                $res[$y] += $value;
            }
            $csv = CsvDistrib::distrib2csv($res);
            // backup file with months as age-M.csv
            $bck_file = str_replace('age.csv', 'age-M.csv', $file);
            copy($file, $bck_file);
            // write new version with years instead of months
            file_put_contents($file, $csv);
        }
        return "Done\n";
    }
    
} // end class
