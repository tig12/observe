<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:49:38+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\shared;

use observe\model\ICommand;

class chi2 implements ICommand {
    
    /**
    **/
    public static function execute(array $studyFile, array $params): string {
    
        return 'observe\studies\shared\chi2';
    }
    
} // end class
