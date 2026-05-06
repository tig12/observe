<?php
/******************************************************************************
    
    Interface implemented by classes directly called by the CLI.
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-03-11, Thierry Graff : Creation
********************************************************************************/

namespace observe\app;

use observe\model\IStudy;

interface ICommand {
    
    /** 
        @param  $study      Study object
        @param  $params     Regular array of parameters passed to run-observe.php when calling the command.
    **/
    public static function execute(IStudy $study, array $params): string;
    
} // end class

