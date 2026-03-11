<?php
/******************************************************************************

    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-03-11 17:47:41+01:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\studies\death_fr;

use observe\model\ICommand;

class split implements ICommand {
    
    /**
    **/
    public static function execute(array $studyFile, array $params): string {
    
        return 'observe\studies\death_fr\split';
    }
    
} // end class
