<?php
/******************************************************************************
    
    Contains code tu move somewhere else
    
    @license    GPL - conforms to file LICENCE located in root directory of current repository.
    @copyright  Thierry Graff
    
    @history    2026-05-02 23:26:28+02:00, Thierry Graff : Creation
********************************************************************************/

namespace observe\model;

class Misc {
    
    /**
        Builds a code from two values of an array.
        The code is built respecting the order of the values.
        Ex: if $values = ['SO', 'MO', 'ME', 'JU']
               pairCode('SO', 'MO') returns 'SO-MO'
               pairCode('MO', 'SO') also returns 'SO-MO' because SO if before MO in $values.
        @param  $
    **/
    public static function pairCode(string $v1, string $v2, array $values): string {
        $res = '';
        $found1 = false;
        foreach($values as $v){
            if($v == $v1 || $v == $v2){
                if($found1){
                    return $res . '-' . $v;
                }
                else{
                    $res = $v;
                }
                $found1 = true;
            }
        }
        return ''; // Normally never reached    
    }
    
} // end class
