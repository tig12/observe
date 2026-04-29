<?php
/******************************************************************************
    
    Command used during development, but not for production use
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-04-29 10:00:27+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands;

use observe\app\ICommand;
use observe\model\IStudy;

use observe\commands\dev\all;

class dev implements ICommand {
    
    private const POSSIBLE_ACTIONS = [
        'all' => 'Execute all commands on a given study',
    ];
    
    
    /** 
        Called by Commands::runCommand)
    **/
    public static function execute(IStudy $study, array $params): string {
        //
        // Parameter check
        //
        $usage = "Usage of this command: php run-observe <study> dev <action>\n<action> can be:\n";
        foreach(self::POSSIBLE_ACTIONS as $k => $v){
            $usage .= str_pad("    $k:", 16) . "$v\n";
        }
        if(count($params) != 1){
            return "WRONG NUMBER OF ARGUMENTS.\n$usage";
        }
        
        $action = $params[0];
        
        if(!in_array($action, array_keys(self::POSSIBLE_ACTIONS))){
            return "INVALID VALUE FOR <action>: \"$action\".\n$usage";
        }
        //
        // Execute
        //
        switch($action){
        	case 'all': all::execute($study); break;
        }
        return '';
    }
    
} // end class
