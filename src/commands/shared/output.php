<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:50:55+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\shared;

use observe\model\ICommand;

class output implements ICommand {
    
    /**
    **/
    public static function execute(array $studyConfig, array $params): string {
    
        return 'observe\studies\shared\output';
    }
    
} // end class
