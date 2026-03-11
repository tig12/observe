<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:50:23+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\shared;

use observe\model\ICommand;

class distrib implements ICommand {
    
    /**
    **/
    public static function execute(array $studyFile, array $params): string {
    
        return 'observe\studies\shared\distrib';
    }
    
} // end class
