<?php
/******************************************************************************
    Common code for MFC (Mother Father Child) output generation.
    
    @license    GPL
    @history    2021-01-30 06:23:48+01:00, Thierry Graff : Creation
********************************************************************************/
namespace observe\commands\mfc;

class MFC {
    
    const M = 'Mother';
    const F = 'Father';
    const W = 'Wedding';
    const C = 'Child';
    
    const LABELS = [
        'M' => 'Mother',
        'F' => 'Father',
        'C' => 'Child',
        'W' => 'Wedding',
    ];
    
    /**
        Computes the possible combinations of members in a MFCW experience.
        @param  $wedding    Include wedding in couple computation ?
    **/
    public static function computeCouples(bool $wedding) {
        $members = ['M', 'F', 'C'];
        if($wedding){
            $members[] = 'W';
        }
        $couples = [];
        for($i=0; $i < count($members); $i++){
            for($j=$i+1; $j < count($members); $j++){
                $couples[] = [$members[$i], $members[$j]];
            }
        }
        return $couples;
    }
    
    
} // end class
