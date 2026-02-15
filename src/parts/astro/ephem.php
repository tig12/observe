<?php
/******************************************************************************
    Auxiliary code to assist ephemeris computation
    
    @license    GPL
    @history    2026-02-13 21:05:57+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\parts\astro;

use tigeph\model\IAA;

class ephem {
    
    /**
        Converts IAA planet codes to tigeph constants (defined in tigeph.model.SysolC).
        @param  $iaaCodes Array of IAA planet codes. Ex: ['SO', 'MO', 'ME', 'VE', 'MA', 'JU', 'SA', 'UR', 'NE', 'PL', 'NN']
        @return Array of equivalent tigeph codes that can be used to call an implementation of tigeph.Ephem.ephem()
    **/
    public static function iaa2tigeph(array $iaaCodes): array {
        return array_values(array_intersect_key(IAA::IAA_TIGEPH, array_flip($iaaCodes)));
    }
    
}// end class
