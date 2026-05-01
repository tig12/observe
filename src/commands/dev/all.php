<?php
/******************************************************************************
    
    Executes all the steps of a study, from import to output generation
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-04-29 10:09:50+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\dev;

use observe\model\IStudy;

class all {
    
    public static function execute(IStudy $study): string {
        
        echo "============================= dev - init =============================\n";
        $study->init([]);
        
        echo "============================= dev - import =============================\n";
        $study->import([]);
        
        echo "============================= dev - observed =============================\n";
        $study->observed([]);
        
        echo "============================= dev - control =============================\n";
        $study->control([1]);
        
        echo "============================= dev - expected =============================\n";
        $study->expected([]);
        
        echo "============================= dev - stats =============================\n";
        $study->stats([]);
        
        echo "============================= dev - dim2 =============================\n";
        $study->dim2([]);
        
        echo "============================= dev - ouput img =============================\n";
        $study->output(['img', 'all']);
        
        echo "============================= dev - ouput page =============================\n";
        $study->output(['page', 'all']);
        
        return '';
    }
    
} // end class
