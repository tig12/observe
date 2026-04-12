<?php
/******************************************************************************

    Generates the html pages to visualize the results of a given study.

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:50:55+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use observe\model\ICommand;
use observe\commands\shared\output\output_page;
use observe\commands\shared\output\output_img;

class output implements ICommand {
    
    const array POSSIBLE_ACTIONS = [
        'page'          => 'Generate html page(s) of the output',
        'img'           => 'Generate images included in html pages',
    ];
    
    
    /**
        Called by Studies::runCommand()
    **/
    public static function execute(array &$studyConfig, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe <study> output <action> <object>\n"
            . "<action> can be:\n";
            foreach(self::POSSIBLE_ACTIONS as $k => $v){
                $usage .= str_pad("    $k:", 17) . "$v\n";
            }
            $usage .= "If <action> = \"page\", <object> can be:\n";
            foreach(output_page::POSSIBLE_PAGES as $k => $v){
                $usage .= str_pad("    $k:", 17) . "$v\n";
            }
            $usage .= "If <action> = \"img\", <object> can be:\n";
            foreach(output_img::POSSIBLE_IMG as $k => $v){
                $usage .= str_pad("    $k:", 17) . "$v\n";
            }
        if(count($params) != 2){
            return "WRONG NUMBER OF ARGUMENTS.\n$usage";
        }
        //
        // Execution
        //
        if($params[0] == 'page'){
            $msg = output_page::execute($studyConfig, $params);
        }
        else{
            $msg = output_img::execute($studyConfig, $params);
        }
        return ($msg != '' ? "$msg\n$usage" : '');
    }
    
} // end class
