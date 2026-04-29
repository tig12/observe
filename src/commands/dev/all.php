<?php
/******************************************************************************
    
    Executes all the steps of a study, from import to output generation
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    @history    2026-04-29 10:09:50+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\commands\dev;

use observe\model\IStudy;

use observe\studies\death_fr\init;
use observe\studies\death_fr\import;
use observe\commands\observed;
use observe\studies\death_fr\control;
use observe\commands\expected;
use observe\commands\stats;
use observe\commands\dim2;
use observe\commands\output;

class all {
    
    public static function execute(IStudy $study): string {
        
        // TODO This mechanism must be modified to become generic => see Commands::runCommand() and Studies::getStudyClasspath()
        
        echo "============================= dev - init =============================\n";
        init::execute($study, []);
        
        echo "============================= dev - import =============================\n";
        import::execute($study, []);
        
        echo "============================= dev - observed =============================\n";
        observed::execute($study, []);
        
        echo "============================= dev - control =============================\n";
        control::execute($study, [1]);
        
        echo "============================= dev - expected =============================\n";
        expected::execute($study, []);
        
        echo "============================= dev - stats =============================\n";
        stats::execute($study, []);
        
        echo "============================= dev - dim2 =============================\n";
        dim2::execute($study, []);
        
        echo "============================= dev - ouput img =============================\n";
        output::execute($study, ['img', 'all']);
        
        echo "============================= dev - ouput page =============================\n";
        output::execute($study, ['page', 'all']);
        
        return '';
    }
    
} // end class
