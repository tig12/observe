<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:48:18+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\death_fr;

use observe\model\ICommand;

class control implements ICommand {
    
    /**
    **/
    public static function execute(array $studyConfig, array $params): string {
    
        return 'observe\studies\death_fr\control';
    }
    
} // end class
