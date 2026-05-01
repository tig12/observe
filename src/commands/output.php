<?php
/******************************************************************************

    Generates the html pages to visualize the results of a given study.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:50:55+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands;

use observe\app\ICommand;
use observe\model\IStudy;
use observe\commands\output\output_page;
use observe\commands\output\output_img;
use tiglib\time\seconds2HHMMSS;

class output implements ICommand {
    
    const array POSSIBLE_ACTIONS = [
        'page'          => 'Generate html page(s) of the output',
        'img'           => 'Generate images included in html pages',
    ];
    
    /**
        Called by Run::runCommand()
    **/
    public static function execute(IStudy $study, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe <study> output <action> <object>\n<action> can be:\n";
        foreach(self::POSSIBLE_ACTIONS as $k => $v){
            $usage .= str_pad("    $k:", 16) . "$v\n";
        }
        $usage .= "    If <action> = \"page\", <object> can be:\n";
        foreach(output_page::POSSIBLE_PAGES as $k => $v){
            $usage .= str_pad("        $k:", 24) . "$v\n";
        }
        $usage .= "    If <action> = \"img\", <object> can be:\n";
        foreach(output_img::POSSIBLE_IMG as $k => $v){
            $usage .= str_pad("        $k:", 24) . "$v\n";
        }
        if(count($params) != 2){
            return "WRONG NUMBER OF ARGUMENTS.\n$usage";
        }
        //
        // Execution
        //
        
        $t1 = microtime(true);
        
        if($params[0] == 'page'){
            $msg = output_page::execute($study, $params);
        }
        else{
            $msg = output_img::execute($study, $params);
        }
        
        $t2 = microtime(true);
        $dt = round($t2 - $t1, 3);
        $dth = seconds2HHMMSS::compute($dt);
        echo "Execution time $dt s - $dth\n";
        
        return ($msg != '' ? "$msg\n$usage" : '');
    }
    
} // end class
